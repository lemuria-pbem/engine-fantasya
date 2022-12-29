<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Exception\LemuriaException;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Intelligence;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Unit;

/**
 * A campaign is a collection of all battles in a region, where all attacking and defending units fight in one or more
 * battles.
 */
class Campaign
{
	use MessageTrait;

	/**
	 * @var array<int, Unit>
	 */
	private array $armies = [];

	/**
	 * @var array<int, array<int>>
	 */
	private array $attackers = [];

	/**
	 * @var array<int, array<int>>
	 */
	private array $defenders = [];

	/**
	 * @var array<int, Battle>|null
	 */
	private ?array $battles = null;

	private Intelligence $intelligence;

	/**
	 * @var array<int, Stake>
	 */
	private array $status = [];

	/**
	 * @var array<int, BattlePlan>
	 */
	private array $partyBattle = [];

	public function __construct(private Region $region) {
		$this->intelligence = new Intelligence($this->region);
		foreach ($this->intelligence->getParties() as $party) {
			$this->status[$party->Id()->Id()] = Stake::Neutral;
		}
		Lemuria::Log()->debug('Beginning new campaign in ' . $this->region . '.');
	}

	public function Region(): Region {
		return $this->region;
	}

	/**
	 * @return Battle[]
	 */
	public function Battles(): array {
		return $this->battles ?? [];
	}

	public function addAttack(Unit $attacker, Unit $defender): Side {
		$attackId = $attacker->Id()->Id();
		$defendId = $defender->Id()->Id();
		if (isset($this->defenders[$attackId])) {
			Lemuria::Log()->debug($attacker . ' is already attacked. Attack against ' . $defender . ' cancelled.');
			if (isset($this->attackers[$defendId])) {
				return Side::Defender;
			}
			$this->armies[$defendId]      = $defender;
			$this->attackers[$defendId][] = $attackId;
			$this->defenders[$attackId][] = $defendId;
			Lemuria::Log()->debug($defender . ' is attacked by ' . $attacker . ' and joins the battle as attacker.');
			return Side::Involve;
		}

		$this->armies[$attackId]      = $attacker;
		$this->armies[$defendId]      = $defender;
		$this->attackers[$attackId][] = $defendId;
		$this->defenders[$defendId][] = $attackId;
		Lemuria::Log()->debug($attacker . ' attacks ' . $defender . ' in ' . $this->region . '.');
		return Side::Attacker;
	}

	public function mount(): bool {
		if (is_array($this->battles)) {
			return false;
		}

		$this->battles = [];
		$defenders     = $this->createDefenderBattles();
		$this->addDefenderOtherUnits($defenders);
		$this->addDefenderAlliedUnits();
		$this->mergeAttackerBattles();
		$this->battles = array_values($this->battles);
		$n             = count($this->battles);
		for ($i = 0; $i < $n; $i++) {
			$this->battles[$i]->counter = $i + 1;
		}
		Lemuria::Log()->debug('Campaign in ' . $this->region . ' consists of ' . count($this->battles) . ' battles.');
		return true;
	}

	/**
	 * @return array(int=>true)
	 */
	private function createDefenderBattles(): array {
		$defenders     = [];
		$attackersLeft = array_fill_keys(array_keys($this->attackers), true);
		foreach ($this->defenders as $defId => $attackers) {
			$party  = $this->party($defId);
			$battle = $this->battle($party);
			$unit   = $this->armies[$defId];
			$battle->addDefender($unit);
			$id                = $party->Id()->Id();
			$this->status[$id] = Stake::Defender;
			$defenders[$id]    = true;
			Lemuria::Log()->debug($unit . ' is attacked in battle of defender ' . $party . '.');

			foreach ($attackers as $attId) {
				if (!isset($this->attackers[$attId])) {
					throw new LemuriaException('Attacker ' . $attId . ' not found.');
				}
				$unit = $this->armies[$attId];
				if (isset($attackersLeft[$attId])) {
					$battle->addAttacker($unit);
					$party                  = $this->party($attId);
					$partyId                = $party->Id()->Id();
					$this->status[$partyId] = Stake::Attacker;
					unset($attackersLeft[$attId]);
					Lemuria::Log()->debug($unit . ' attacks in battle of attacker ' . $party . '.');
				} else {
					Lemuria::Log()->debug($unit . ' is already attacking in battle of attacker ' . $party . '.');
				}
			}
		}
		return array_keys($defenders);
	}

	private function addDefenderOtherUnits(array $defenders): void {
		foreach ($defenders as $partyId) {
			$party = Party::get(new Id($partyId));
			if ($party->Type() === Type::Player) {
				$battle = $this->battle($party);
				foreach ($this->intelligence->getUnits($party) as $unit /* @var Unit $unit */) {
					if ($unit->BattleRow() >= BattleRow::Defensive->value) {
						$id = $unit->Id()->Id();
						if (!isset($this->defenders[$id])) {
							$battle->addDefender($unit);
							Lemuria::Log()->debug($unit . ' gets drawn into battle as defender.');
						}
					}
				}
			}
		}
	}

	private function addDefenderAlliedUnits(): void {
		foreach ($this->status as $alliedId => $neutral) {
			if ($neutral === Stake::Neutral) {
				$ally = Party::get(new Id($alliedId));
				foreach ($this->status as $partyId => $defender) {
					if ($defender === Stake::Defender) {
						$party = Party::get(new Id($partyId));
						if ($ally->Diplomacy()->has(Relation::COMBAT, $party)) {
							$battle = $this->battle($party);
							foreach ($this->intelligence->getUnits($ally) as $unit /* @var Unit $unit */) {
								if ($unit->BattleRow() >= BattleRow::Defensive->value) {
									$battle->addDefender($unit);
									Lemuria::Log()->debug($unit . ' gets drawn into battle as ally.');
								}
							}
							$this->status[$alliedId] = Stake::Ally;
						}
					}
				}
			}
		}
	}

	private function mergeAttackerBattles(): void {
		foreach ($this->attackers as $defenders) {
			$battles = [];
			foreach ($defenders as $defId) {
				$party            = $this->party($defId)->Id()->Id();
				$battle           = $this->partyBattle[$party]->getBattle(Place::Region);
				$battles[$battle] = true;
			}
			$battles = array_keys($battles);
			$count   = count($battles);
			if ($count >= 2) {
				$all   = implode(',', $battles);
				$first = $battles[0];
				Lemuria::Log()->debug('Merging ' . $count . ' battles #' . $all . ' into #' . $first . ' for common attacker.');
			}
			while (count($battles) > 1) {
				$second = array_pop($battles);
				$first  = array_pop($battles);
				/** @var Battle $firstBattle */
				$firstBattle = $this->battles[$first];
				/** @var Battle $secondBattle */
				$secondBattle = $this->battles[$second];
				$firstBattle->merge($secondBattle);
				unset($this->battles[$second]);
				$battles[] = $first;
			}
			if (count($this->battles) <= 1) {
				break;
			}
		}
	}

	protected function party(int $id): Party {
		if (isset($this->armies[$id])) {
			$unit = $this->armies[$id];
			return $unit->Party();
		}
		throw new \InvalidArgumentException();
	}

	protected function battle(Party $party): Battle {
		$id = $party->Id()->Id();
		if (isset($this->partyBattle[$id])) {
			return $this->battles[$this->partyBattle[$id]->getBattle(Place::Region)];
		}

		foreach ($this->partyBattle as $partyId => $battlePlan) {
			$otherParty = Party::get(new Id($partyId));
			if ($otherParty->Diplomacy()->has(Relation::COMBAT, $party)) {
				$this->partyBattle[$id] = $battlePlan;
				$battleId               = $battlePlan->getBattle(Place::Region);
				Lemuria::Log()->debug('Allied party ' . $party . ' enters battle #' . $battleId . ' in ' . $this->region . '.');
				return $this->battles[$battleId];
			}
		}

		$battle                 = new Battle($this->region);
		$battleId               = count($this->battles);
		$this->partyBattle[$id] = new BattlePlan($battleId);
		$this->battles[]        = $battle;
		Lemuria::Log()->debug('New battle #' . $battleId . ' in ' . $this->region . ' started for defender ' . $party . '.');
		return $battle;
	}
}
