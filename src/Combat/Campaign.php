<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat;
use Lemuria\Model\Fantasya\Intelligence;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Unit;

/**
 * A campaign is a collection of all battles in a region, where all attacking and defending units fight in one or more
 * battles.
 */
class Campaign
{
	protected const NEUTRAL = 0;

	protected const ALLY = 1;

	protected const DEFENDER = 2;

	protected const ATTACKER = 3;

	/**
	 * @var array(int=>Unit)
	 */
	private array $armies = [];

	/**
	 * @var array(int=>array)
	 */
	private array $attackers = [];

	/**
	 * @var array(int=>array)
	 */
	private array $defenders = [];

	/**
	 * @var Battle[]|null
	 */
	private ?array $battles = null;

	private Intelligence $intelligence;

	/**
	 * @var array(int=>int)
	 */
	private array $status = [];

	/**
	 * @var array(int=>int)
	 */
	private array $partyBattle = [];

	public function __construct(private Region $region) {
		$this->intelligence = new Intelligence($this->region);
		foreach ($this->intelligence->getParties() as $party) {
			$this->status[$party->Id()->Id()] = self::NEUTRAL;
		}
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

	public function addAttack(Unit $attacker, Unit $defender): Campaign {
		$attackId                     = $attacker->Id()->Id();
		$this->armies[$attackId]      = $attacker;
		$defendId                     = $defender->Id()->Id();
		$this->armies[$defendId]      = $defender;
		$this->attackers[$attackId][] = $defendId;
		$this->defenders[$defendId][] = $attackId;
		return $this;
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
		return true;
	}

	protected function party(int $id): Party {
		if (isset($this->armies[$id])) {
			/** @var Unit $unit */
			$unit = $this->armies[$id];
			return $unit->Party();
		}
		throw new \InvalidArgumentException();
	}

	protected function battle(Party $party): Battle {
		$id = $party->Id()->Id();
		if (isset($this->partyBattle[$id])) {
			return $this->battles[$this->partyBattle[$id]];
		}

		foreach ($this->partyBattle as $partyId => $battleId) {
			$otherParty = Party::get(new Id($partyId));
			if ($otherParty->Diplomacy()->has(Relation::COMBAT, $party)) {
				$this->partyBattle[$id] = $battleId;
				Lemuria::Log()->debug('Allied party ' . $party . ' enters battle #' . $battleId . ' in region ' . $this->region . '.');
				return $this->battles[$battleId];
			}
		}

		$battle                 = new Battle($this->region);
		$battleId               = count($this->battles);
		$this->partyBattle[$id] = $battleId;
		$this->battles[]        = $battle;
		Lemuria::Log()->debug('New battle #' . $battleId . ' in region ' . $this->region . ' started by party ' . $party . '.');
		return $battle;
	}

	/**
	 * @return array(int=>true)
	 */
	private function createDefenderBattles(): array {
		$defenders = [];
		foreach ($this->defenders as $defId => $attackers) {
			$party  = $this->party($defId);
			$battle = $this->battle($party);
			$unit   = $this->armies[$defId];
			$battle->addDefender($unit);
			$id                = $party->Id()->Id();
			$this->status[$id] = self::DEFENDER;
			$defenders[$id]    = true;
			Lemuria::Log()->debug('Unit ' . $unit . ' is attacked in battle of defending party ' . $party . '.');
			foreach ($attackers as $attId) {
				$unit = $this->armies[$attId];
				$battle->addAttacker($unit);
				$party = $this->party($attId)->Id()->Id();
				$this->status[$party] = self::ATTACKER;
				Lemuria::Log()->debug('Unit ' . $unit . ' attacks in battle for party ' . $party . '.');
			}
		}
		return array_keys($defenders);
	}

	private function addDefenderOtherUnits(array $defenders): void {
		foreach ($defenders as $partyId) {
			$party  = Party::get(new Id($partyId));
			$battle = $this->battle($party);
			foreach ($this->intelligence->getUnits($party) as $unit /* @var Unit $unit */) {
				if ($unit->BattleRow() >= Combat::DEFENSIVE) {
					$id = $unit->Id()->Id();
					if (!isset($this->defenders[$id])) {
						$battle->addDefender($unit);
						Lemuria::Log()->debug('Unit ' . $unit . ' gets drawn into battle as defender.');
					}
				}
			}
		}
	}

	private function addDefenderAlliedUnits(): void {
		foreach ($this->status as $alliedId => $neutral) {
			if ($neutral === self::NEUTRAL) {
				$ally = Party::get(new Id($alliedId));
				foreach ($this->status as $partyId => $defender) {
					if ($defender === self::DEFENDER) {
						$party = Party::get(new Id($partyId));
						if ($ally->Diplomacy()->has(Relation::COMBAT, $party)) {
							$battle = $this->battle($party);
							foreach ($this->intelligence->getUnits($ally) as $unit /* @var Unit $unit */) {
								if ($unit->BattleRow() >= Combat::DEFENSIVE) {
									$battle->addDefender($unit);
									Lemuria::Log()->debug('Unit ' . $unit . ' gets drawn into battle as ally.');
								}
							}
							$this->status[$alliedId] = self::ALLY;
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
				$battle           = $this->partyBattle[$party];
				$battles[$battle] = true;
			}
			$battles = array_keys($battles);
			$count   = count($battles);
			if ($count >= 2) {
				Lemuria::Log()->debug('Merging ' . $count . ' battles into one.');
			}
			while (count($battles) > 1) {
				$second  = array_pop($battles);
				$first   = array_pop($battles);
				/** @var Battle $firstBattle */
				$firstBattle = $this->battles[$first];
				/** @var Battle $secondBattle */
				$secondBattle = $this->battles[$second];
				$firstBattle->merge($secondBattle);
				unset($this->battles[$second]);
				$battles[] = $first;
			}
		}
	}
}
