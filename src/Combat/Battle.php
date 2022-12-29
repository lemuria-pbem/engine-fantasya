<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use function Lemuria\randElement;
use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Combat\Log\Message\AttackerWonMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\BattleEndedInDrawMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\BattleEndsMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\BattleExhaustionMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\DefenderWonMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\NoTacticsRoundMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\TakeLootMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\TakeTrophiesMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\UnitDiedMessage;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Effect\ConstructionLoot;
use Lemuria\Engine\Fantasya\Effect\RegionLoot;
use Lemuria\Engine\Fantasya\Effect\VanishEffect;
use Lemuria\Engine\Fantasya\Effect\VesselLoot;
use Lemuria\Engine\Fantasya\Event\Game\Spawn;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Factory\Model\DisguisedParty;
use Lemuria\Engine\Fantasya\Message\Region\AttackInfectedZombiesMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackBoardAfterCombatMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackEnterAfterCombatMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackUnguardMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Monster\Zombie;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Heirs;
use Lemuria\Model\Fantasya\Intelligence;
use Lemuria\Model\Fantasya\Monster;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Talent\Tactics;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Fantasya\Vessel;
use Lemuria\Model\Fantasya\WearResources;

class Battle
{
	use BuilderTrait;
	use MessageTrait;

	protected const EXHAUSTION_ROUNDS = 10;

	protected const WEAR = 0.5;

	protected const WEAR_ROUNDS = 50;

	protected const WEAR_DIVISOR = self::WEAR_ROUNDS ** 2 / self::WEAR;

	public int $counter;

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

	/**
	 * @var array(int=>Unit)
	 */
	private array $monsters = [];

	private Resources $battlefieldRemains;

	private Construction|false|null $construction = false;

	private Vessel|false|null $vessel = false;

	public function __construct(private Region $region) {
		$this->intelligence = new Intelligence($this->region);
		$this->initMonsters();
	}

	public function Region(): Region {
		return $this->region;
	}

	/**
	 * @return array<Party>
	 */
	public function Attacker(): array {
		return $this->getParties($this->attackers);
	}

	/**
	 * @return array<Party>
	 */
	public function Defender(): array {
		return $this->getParties($this->defenders);
	}

	public function Construction(): Construction|false|null {
		return $this->construction;
	}

	public function Vessel(): Vessel|false|null {
		return $this->vessel;
	}

	public function addAttacker(Unit $unit): Battle {
		$this->attackers[] = $unit;
		$id                = $unit->Id()->Id();
		if (isset($this->monsters[$id])) {
			foreach ($this->monsters[$id] as $monster /* @var Unit $monster */) {
				$this->attackers[] = $monster;
				Lemuria::Log()->debug('Monster ' . $monster . ' supports attacker in battle.');
			}
		}
		if ($this->construction === false) {
			$this->construction = $unit->Construction();
		}
		if ($this->vessel === false) {
			$this->vessel = $unit->Vessel();
		}
		return $this;
	}

	public function addDefender(Unit $unit): Battle {
		$this->defenders[] = $unit;
		$id                = $unit->Id()->Id();
		if (isset($this->monsters[$id])) {
			foreach ($this->monsters[$id] as $monster /* @var Unit $monster */) {
				$this->defenders[] = $monster;
				Lemuria::Log()->debug('Monster ' . $monster . ' supports defender in battle.');
			}
		}
		$construction = $unit->Construction();
		if ($this->construction !== null) {
			$this->construction = $construction;
		}
		$vessel = $unit->Vessel();
		if ($this->vessel !== null) {
			$this->vessel = $vessel;
		}
		return $this;
	}

	public function commence(Context $context): Battle {
		if (empty($this->attackers)) {
			throw new \RuntimeException('No attackers in battle.');
		}
		if (empty($this->defenders)) {
			throw new \RuntimeException('No defenders in battle.');
		}

		$combat = $this->embattleForCombat($context);
		$party  = $this->getBestTacticsParty();
		$combat->castPreparationSpells($party);
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
		BattleLog::getInstance()->add(new BattleEndsMessage());
		$this->treatInjuredUnits();
		return $this->takeLoot($combat)->addBattlefieldRemains()->createNewZombies($combat);
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

	/**
	 * @return array<Party>
	 */
	protected function getParties(array $units): array {
		$parties = [];
		foreach ($units as $unit /* @var Unit $unit */) {
			$disguise = $unit->Disguise();
			if ($disguise) {
				$party = new DisguisedParty($unit->Party());
				$party->setDisguise($disguise);
			} elseif ($disguise === null) {
				$party = new DisguisedParty($unit->Party());
			} else {
				$party = $unit->Party();
			}
			$id           = $party->Id()->Id();
			$parties[$id] = $party;
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

		$n = count($tactics);
		if ($n <= 0) {
			return null;
		}

		arsort($tactics);
		$candidates = [0];
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
		$chosen = randElement($candidates);
		return Party::get(new Id($party[$chosen]));
	}

	protected function embattleForCombat(Context $context): Combat {
		$combat = new Combat($context);
		foreach ($this->attackers as $unit) {
			$combat->addAttacker($unit);
		}
		foreach ($this->defenders as $unit) {
			$combat->addDefender($unit);
		}
		$this->attackArmies = $combat->getAttackers();
		$this->defendArmies = $combat->getDefenders();
		return $combat->embattle();
	}

	protected function takeLoot(Combat $combat): Battle {
		$rounds       = $combat->getRounds();
		$attackerLoot = $this->collectLoot($this->attackArmies, $rounds);
		$defenderLoot = $this->collectLoot($this->defendArmies, $rounds);

		$this->battlefieldRemains = new Resources();
		if ($combat->hasAttackers()) {
			if ($combat->hasDefenders()) {
				Lemuria::Log()->debug('Battle ended in a draw due to exhaustion (' . self::EXHAUSTION_ROUNDS . ' rounds without damage).');
				BattleLog::getInstance()->add(new BattleExhaustionMessage(self::EXHAUSTION_ROUNDS));
				$heirs = $this->intelligence->getHeirs($this->attackers[0]);
				$this->giveLootToHeirs($heirs, $attackerLoot);
				$heirs = $this->intelligence->getHeirs($this->defenders[0]);
				$this->giveLootToHeirs($heirs, $defenderLoot);
			} else {
				Lemuria::Log()->debug('Attacker has won the battle, defender is defeated.');
				BattleLog::getInstance()->add(new AttackerWonMessage());
				$heirs = $this->intelligence->getHeirs($this->attackers[0]);
				$this->giveLootToHeirs($heirs, $attackerLoot);
				$this->giveLootToHeirs($heirs, $defenderLoot);
				$this->takeTrophies($heirs, $this->defendArmies);
			}
		} else {
			if ($combat->hasDefenders()) {
				Lemuria::Log()->debug('Defender has won the battle, attacker is defeated.');
				BattleLog::getInstance()->add(new DefenderWonMessage());
				$heirs = $this->intelligence->getHeirs($this->defenders[0]);
				$this->giveLootToHeirs($heirs, $attackerLoot);
				$this->giveLootToHeirs($heirs, $defenderLoot);
				$this->takeTrophies($heirs, $this->attackArmies);
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

	protected function collectLoot(array $armies, int $rounds): Resources {
		$loot = new WearResources();
		$wear = min(self::WEAR, $rounds ** 2 / self::WEAR_DIVISOR);
		$loot->setWear($wear);
		foreach ($armies as $army /* @var Army $army */) {
			$loot->fill($army->Loss());
		}
		return $loot;
	}

	protected function giveLootToHeirs(Heirs $heirs, Resources $loot): void {
		foreach ($loot as $quantity /* @var Quantity $quantity */) {
			$unit  = $heirs->random();
			$party = $unit->Party();
			$type  = $party->Type();
			if ($type === Type::Player) {
				if ($party->Loot()->wants($quantity->Commodity())) {
					$unit->Inventory()->add(new Quantity($quantity->Commodity(), $quantity->Count()));
					Lemuria::Log()->debug($unit . ' takes loot: ' . $quantity);
					BattleLog::getInstance()->add(new TakeLootMessage($unit, $quantity));
				} else {
					$this->battlefieldRemains->add($quantity);
					Lemuria::Log()->debug($unit . ' scorns loot ' . $quantity . ', added to battlefield remains.');
				}
			} elseif ($type === Type::Monster) {
				$race = $unit->Race();
				if ($race instanceof Monster) {
					$commodity = $quantity->Commodity();
					if (isset($race->Loot()[$commodity])) {
						$unit->Inventory()->add(new Quantity($commodity, $quantity->Count()));
						Lemuria::Log()->debug($unit . ' takes loot: ' . $quantity);
						BattleLog::getInstance()->add(new TakeLootMessage($unit, $quantity));
					} else {
						Lemuria::Log()->debug($unit . ' scorns loot ' . $quantity);
					}
				}
			}
		}
	}

	protected function takeTrophies(Heirs $heirs, array $armies): void {
		foreach ($armies as $army /* @var Army $army */) {
			foreach ($army->Trophies() as $quantity /* @var Quantity $quantity */) {
				$unit = $heirs->random();
				if ($unit->Party()->Type() === Type::Player) {
					$unit->Inventory()->add(new Quantity($quantity->Commodity(), $quantity->Count()));
					// Lemuria::Log()->debug($unit . ' takes trophies: ' . $quantity);
					BattleLog::getInstance()->add(new TakeTrophiesMessage($unit, $quantity));
				}
			}
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
		$standing  = [];
		$hitpoints = [];
		foreach ($army->Combatants() as $combatant) {
			$unit = $combatant->Unit();
			$id   = $unit->Id()->Id();
			if (!isset($units[$id])) {
				$units[$id]     = $unit;
				$standing[$id]  = 0;
				$hitpoints[$id] = 0;
			}
			foreach ($combatant->fighters as $fighter) {
				$standing[$id]++;
				$hitpoints[$id] += $fighter->health;
			}
			foreach ($combatant->refugees as $fighter) {
				$hitpoints[$id] += $fighter->health;
			}
		}

		foreach ($units as $id => $unit) {
			$size = $unit->Size();
			if ($size <= 0) {
				$this->destroyedUnitLeaves($unit);
				$unit->setIsGuarding(false)->setHealth(0.0);
				Lemuria::Log()->debug('Unit ' . $unit . ' of army ' . $army->Id() . ' is destroyed.');
				BattleLog::getInstance()->add(new UnitDiedMessage($unit));
			} else {
				$this->survivingUnitEnters($unit);
				if ($standing[$id] <= 0 && $unit->IsGuarding()) {
					$unit->setIsGuarding(false);
					$this->message(AttackUnguardMessage::class, $unit);
				}
				$calculus     = new Calculus($unit);
				$maxHitpoints = $calculus->hitpoints();
				$health       = min(1.0, $hitpoints[$id] / ($size * $maxHitpoints));
				$unit->setHealth($health);
				Lemuria::Log()->debug('Unit ' . $unit . ' of army ' . $army->Id() . ' has health ' . $health . '.');
			}
		}
	}

	private function initMonsters(): void {
		$score  = Lemuria::Score();
		$effect = new VanishEffect(State::getInstance());
		$party  = Party::get(Spawn::getPartyId(Type::Monster));
		foreach ($this->intelligence->getUnits($party) as $monster /* @var Unit $monster */) {
			$existing = $score->find($effect->setUnit($monster));
			if ($existing instanceof VanishEffect) {
				$unit = $existing->Summoner();
				if ($unit) {
					$id = $unit->Id()->Id();
					if (!isset($this->monsters[$id])) {
						$this->monsters[$id] = [];
					}
					$this->monsters[$id][] = $monster;
				}
			}
		}
	}

	private function survivingUnitEnters(Unit $unit): void {
		$construction = $unit->Construction();
		if ($construction) {
			$this->message(AttackEnterAfterCombatMessage::class, $unit)->e($construction);
			return;
		}
		$vessel = $unit->Vessel();
		if ($vessel) {
			$this->message(AttackBoardAfterCombatMessage::class, $unit)->e($vessel);
		}
	}

	private function destroyedUnitLeaves(Unit $unit): void {
		$construction = $unit->Construction();
		if ($construction) {
			$construction->Inhabitants()->remove($unit);
			return;
		}
		$unit->Vessel()?->Passengers()->remove($unit);
	}

	private function addBattlefieldRemains(): Battle {
		if ($this->battlefieldRemains->count()) {
			if ($this->construction) {
				$effect   = new ConstructionLoot(State::getInstance());
				$existing = Lemuria::Score()->find($effect->setConstruction($this->construction));
			} elseif ($this->vessel) {
				$effect   = new VesselLoot(State::getInstance());
				$existing = Lemuria::Score()->find($effect->setVessel($this->vessel));
			} else {
				$effect   = new RegionLoot(State::getInstance());
				$existing = Lemuria::Score()->find($effect->setRegion($this->region));
			}
			if ($existing) {
				$effect = $existing;
			} else {
				Lemuria::Score()->add($effect);
			}
			foreach ($this->battlefieldRemains as $quantity /* @var Quantity $quantity */) {
				$effect->Resources()->add($quantity);
				Lemuria::Log()->debug('Adding ' . $quantity . ' to battlefield remains.');
			}
		}
		return $this;
	}

	private function createNewZombies(Combat $combat): Battle {
		$size = $combat->getNewZombies();
		if ($size > 0) {
			$region = $this->region->Id()->Id();
			$state  = State::getInstance();
			$spawn  = new Spawn($state);
			$state->injectIntoTurn($spawn->setOptions([
				Spawn::PARTY => Spawn::ZOMBIES, Spawn::REGION => $region, Spawn::SIZE => $size, Spawn::RACE => Zombie::class
			]));
			$this->message(AttackInfectedZombiesMessage::class, $this->region)->p($size)->s(self::createRace(Zombie::class));
		}
		return $this;
	}
}
