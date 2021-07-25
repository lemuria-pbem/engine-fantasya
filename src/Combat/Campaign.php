<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Id;
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
	/**
	 * @var array(int=>Army)
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

	/**
	 * @var array(int=>int)
	 */
	private array $parties = [];

	#[Pure] public function __construct(private Region $region) {
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

	public function getArmy(Unit $unit): Army {
		$army = $this->findArmy($unit);
		if (!$army) {
			$army = new Army($unit->Party());
			$this->addArmy($army->add($unit));
		}
		return $army;
	}

	public function addAttack(Army $attacker, Army $defender): Campaign {
		$attackId                     = $this->addArmy($attacker);
		$defendId                     = $this->addArmy($defender);
		$this->attackers[$attackId][] = $defendId;
		$this->defenders[$defendId][] = $attackId;
		return $this;
	}

	public function mount(): bool {
		if (is_array($this->battles)) {
			return false;
		}

		$this->battles = [];
		$this->createDefenderBattles();
		$this->mergeAttackerBattles();
		$this->battles = array_values($this->battles);
		$this->parties = [];
		return true;
	}

	protected function findArmy(Unit $unit): ?Army {
		foreach ($this->armies as $army) {
			if ($army->Units()->has($unit->Id())) {
				return $army;
			}
		}
		return null;
	}

	protected function addArmy(Army $army): int {
		$id = $army->Id();
		if (!isset($this->armies[$id])) {
			$this->armies[$id] = $army;
		}
		return $id;
	}

	protected function army(int $id): Army {
		if (isset($this->armies[$id])) {
			/** @var Army $army */
			$army = $this->armies[$id];
			return $army;
		}
		throw new \InvalidArgumentException();
	}

	protected function party(int $armyId): Party {
		return $this->army($armyId)->Party();
	}

	protected function battle(Party $party): Battle {
		$id = $party->Id()->Id();
		if (isset($this->parties[$id])) {
			return $this->battles[$this->parties[$id]];
		}

		foreach ($this->parties as $partyId => $battleId) {
			$otherParty = Party::get(new Id($partyId));
			if ($otherParty->Diplomacy()->has(Relation::COMBAT, $party)) {
				$this->parties[$id] = $battleId;
				return $this->battles[$battleId];
			}
		}

		$battle             = new Battle();
		$battleId           = count($this->battles);
		$this->parties[$id] = $battleId;
		$this->battles[]    = $battle;
		return $battle;
	}

	private function createDefenderBattles(): void {
		foreach ($this->defenders as $defId => $attackers) {
			$party  = $this->party($defId);
			$battle = $this->battle($party);
			$battle->addDefender($this->army($defId));
			foreach ($attackers as $attId) {
				$battle->addAttacker($this->army($attId));
			}
		}
	}

	private function mergeAttackerBattles(): void {
		foreach ($this->attackers as $defenders) {
			$battles = [];
			foreach ($defenders as $defId) {
				$party            = $this->party($defId)->Id()->Id();
				$battle           = $this->parties[$party];
				$battles[$battle] = true;
			}
			$battles = array_keys($battles);
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
