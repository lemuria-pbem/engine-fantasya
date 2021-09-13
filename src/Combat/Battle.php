<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Combat\Log\Message\AttackerWonMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\BattleEndedInDrawMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\BattleExhaustionMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\DefenderWonMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\NoTacticsRoundMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\TakeLootMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\UnitDiedMessage;
use Lemuria\Engine\Fantasya\Factory\Model\DisguisedParty;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Gathering;
use Lemuria\Model\Fantasya\Heirs;
use Lemuria\Model\Fantasya\Intelligence;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Talent\Tactics;
use Lemuria\Model\Fantasya\Unit;

class Battle
{
	protected const EXHAUSTION_ROUNDS = 10;

	/**
	 * @var Unit[]
	 */
	private array $attackers = [];

	/**
	 * @var array(int=>Army)
	 */
	private array $attackArmies = [];

	/**
	 * @var Unit[]
	 */
	private array $defenders = [];

	/**
	 * @var array(int=>Army)
	 */
	private array $defendArmies = [];

	private Intelligence $intelligence;

	#[Pure] public function __construct(private Region $region) {
		$this->intelligence = new Intelligence($this->region);
	}

	public function Region(): Region {
		return $this->region;
	}

	public function Attacker(): Gathering {
		return $this->getParties($this->attackers);
	}

	public function Defender(): Gathering {
		return $this->getParties($this->defenders);
	}

	public function addAttacker(Unit $unit): Battle {
		$this->attackers[] = $unit;
		return $this;
	}

	public function addDefender(Unit $unit): Battle {
		$this->defenders[] = $unit;
		return $this;
	}

	public function commence(): Battle {
		if (empty($this->attackers)) {
			throw new \RuntimeException('No attackers in battle.');
		}
		if (empty($this->defenders)) {
			throw new \RuntimeException('No defenders in battle.');
		}

		$combat = $this->embattleForCombat();
		$party  = $this->getBestTacticsParty();
		if ($party) {
			$combat->tacticsRound($party);
		} else {
			Lemuria::Log()->debug('Both sides are tactically equal.');
			BattleLog::getInstance()->add(new NoTacticsRoundMessage());
		}
		$countNoDamage = 0;
		while ($combat->hasAttackers() && $combat->hasDefenders()) {
			$damage = $combat->nextRound();
			if ($damage > 0) {
				$countNoDamage = 0;
			} else {
				if (++$countNoDamage >= self::EXHAUSTION_ROUNDS) {
					break;
				}
			}
		}
		$this->treatInjuredUnits();
		return $this->takeLoot($combat);
	}

	public function merge(Battle $battle): Battle {
		$armies = [];
		foreach ($this->attackers as $unit) {
			$armies[$unit->Id()->Id()] = $unit;
		}
		foreach ($battle->attackers as $unit) {
			$armies[$unit->Id()->Id()] = $unit;
		}
		$this->attackers = array_values($armies);

		$armies = [];
		foreach ($this->defenders as $unit) {
			$armies[$unit->Id()->Id()] = $unit;
		}
		foreach ($battle->defenders as $unit) {
			$armies[$unit->Id()->Id()] = $unit;
		}
		$this->defenders = array_values($armies);

		return $this;
	}

	protected function getParties(array $units): Gathering {
		$parties = new Gathering();
		foreach ($units as $unit /* @var Unit $unit */) {
			$disguise = $unit->Disguise();
			if ($disguise) {
				$parties->add($disguise);
			} elseif ($disguise === null) {
				$parties->add(new DisguisedParty());
			} else {
				$parties->add($unit->Party());
			}
		}
		return $parties;
	}

	/**
	 * @noinspection DuplicatedCode
	 */
	protected function getBestTacticsParty(): ?Party {
		$tactics    = [];
		$isAttacker = [];
		foreach ($this->attackers as $unit) {
			$party = $unit->Party()->Id()->Id();
			if (!isset($tactics[$party])) {
				$tactics[$party]    = 0;
				$isAttacker[$party] = true;
			}
			$calculus         = new Calculus($unit);
			$level            = $calculus->knowledge(Tactics::class)->Level();
			$tactics[$party] += $unit->Size() * $level ** 3;
		}
		foreach ($this->defenders as $unit) {
			$party = $unit->Party()->Id()->Id();
			if (!isset($tactics[$party])) {
				$tactics[$party]    = 0;
				$isAttacker[$party] = false;
			}
			$calculus         = new Calculus($unit);
			$level            = $calculus->knowledge(Tactics::class)->Level();
			$tactics[$party] += $unit->Size() * $level ** 3;
		}

		arsort($tactics);
		$candidates = [0];
		$n          = count($tactics);
		$party      = array_keys($tactics);
		$talent     = array_values($tactics);
		for ($i = 1; $i < $n; $i++) {
			if ($talent[$i] < $talent[0]) {
				break;
			}
			$candidates[] = $i;
		}
		foreach ($candidates as $i) {
			if ($isAttacker[$party[$i]] !== $isAttacker[$party[0]]) {
				return null;
			}
		}
		$chosen = $candidates[array_rand($candidates)];
		return Party::get(new Id($party[$chosen]));
	}

	protected function embattleForCombat(): Combat {
		$combat = new Combat();
		foreach ($this->attackers as $unit) {
			$army                    = $combat->addAttacker($unit);
			$id                      = $army->Id();
			$this->attackArmies[$id] = $army;
		}
		foreach ($this->defenders as $unit) {
			$army                    = $combat->addDefender($unit);
			$id                      = $army->Id();
			$this->defendArmies[$id] = $army;
		}
		return $combat->embattle();
	}

	protected function takeLoot(Combat $combat): Battle {
		$attackerLoot = $this->collectLoot($this->attackArmies);
		$defenderLoot = $this->collectLoot($this->defendArmies);
		if ($combat->hasAttackers()) {
			if ($combat->hasDefenders()) {
				Lemuria::Log()->debug('Battle ended in a draw due to exhaustion (' . self::EXHAUSTION_ROUNDS . ' rounds without damage).');
				BattleLog::getInstance()->add(new BattleExhaustionMessage(self::EXHAUSTION_ROUNDS));
				$heirs = $this->intelligence->getHeirs($this->attackers[0], true);
				$this->giveLootToHeirs($heirs, $attackerLoot);
				$heirs = $this->intelligence->getHeirs($this->defenders[0], true);
				$this->giveLootToHeirs($heirs, $defenderLoot);
			} else {
				Lemuria::Log()->debug('Attacker has won the battle, defender is defeated.');
				BattleLog::getInstance()->add(new AttackerWonMessage());
				$heirs = $this->intelligence->getHeirs($this->attackers[0], true);
				$this->giveLootToHeirs($heirs, $attackerLoot);
				$this->giveLootToHeirs($heirs, $defenderLoot);
			}
		} else {
			if ($combat->hasDefenders()) {
				Lemuria::Log()->debug('Defender has won the battle, attacker is defeated.');
				BattleLog::getInstance()->add(new DefenderWonMessage());
				$heirs = $this->intelligence->getHeirs($this->defenders[0], true);
				$this->giveLootToHeirs($heirs, $attackerLoot);
				$this->giveLootToHeirs($heirs, $defenderLoot);
			} else {
				Lemuria::Log()->debug('Battle ended with both sides defeated each other.');
				BattleLog::getInstance()->add(new BattleEndedInDrawMessage());
				$heirs = $this->intelligence->getHeirs($this->attackers[0], false);
				$this->giveLootToHeirs($heirs, $attackerLoot);
				$this->giveLootToHeirs($heirs, $defenderLoot);
			}
		}
		return $this;
	}

	protected function collectLoot(array $armies): Resources {
		$loot = new Resources();
		foreach ($armies as $army /* @var Army $army */) {
			$loot->fill($army->Loss());
		}
		return $loot;
	}

	protected function giveLootToHeirs(Heirs $heirs, Resources $loot): void {
		foreach ($loot as $quantity /* @var Quantity $quantity */) {
			$unit = $heirs->random();
			$unit->Inventory()->add($quantity);
			Lemuria::Log()->debug($unit . ' takes loot: ' . $quantity);
			BattleLog::getInstance()->add(new TakeLootMessage($unit, $quantity));
		}
	}

	protected function treatInjuredUnits(): void {
		foreach ($this->attackArmies as $army /* @var Army $army */) {
			$this->treatUnitsOfArmy($army);
		}
		foreach ($this->defendArmies as $army /* @var Army $army */) {
			$this->treatUnitsOfArmy($army);
		}
	}

	protected function treatUnitsOfArmy(Army $army): void {
		$units     = [];
		$hitpoints = [];
		foreach ($army->Combatants() as $combatant) {
			$unit = $combatant->Unit();
			$id   = $unit->Id()->Id();
			if (!isset($units[$id])) {
				$units[$id]     = $unit;
				$hitpoints[$id] = 0;
			}
			foreach ($combatant->fighters as $fighter) {
				$hitpoints[$id] += $fighter->health;
			}
		}

		foreach ($units as $id => $unit) {
			$size = $unit->Size();
			if ($size <= 0) {
				$unit->setHealth(0.0);
				Lemuria::Log()->debug('Unit ' . $unit . ' of army ' . $army->Id() . ' is destroyed.');
				BattleLog::getInstance()->add(new UnitDiedMessage($unit));
			} else {
				$calculus     = new Calculus($unit);
				$maxHitpoints = $calculus->hitpoints();
				$health       = $hitpoints[$id] / ($size * $maxHitpoints);
				$unit->setHealth($health);
				Lemuria::Log()->debug('Unit ' . $unit . ' of army ' . $army->Id() . ' has health ' . $health . '.');
			}
		}
	}
}
