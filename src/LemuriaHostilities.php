<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Combat\Battle;
use Lemuria\Engine\Fantasya\Combat\BattleLog;
use Lemuria\Engine\Fantasya\Factory\Model\DisguisedParty;
use Lemuria\Engine\Hostilities;
use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\Model\Location;

class LemuriaHostilities implements Hostilities
{
	/**
	 * @var array<int, array>
	 */
	private array $regionParty = [];

	/**
	 * @var array<int, array>
	 */
	private array $party = [];

	/**
	 * @var array<BattleLog>
	 */
	private array $logs = [];

	private bool $isLoaded = false;

	/**
	 * Search for the battle in a location where a specific entity is engaged.
	 */
	public function find(Location $location, Identifiable $entity): ?Battle {
		$id = $this->regionParty[$location->Id()->Id()][$entity->Id()->Id()] ?? null;
		return $id ? $this->logs[$id] : null;
	}

	/**
	 * Search for all battles in a location.
	 *
	 * @return array<Battle>
	 */
	public function findAll(Location $location): array {
		$id = $location->Id()->Id();
		if (isset($this->regionParty[$id])) {
			$logs = [];
			foreach ($this->regionParty[$id] as $parties) {
				foreach ($parties as $battle) {
					$logs[] = $this->logs[$battle];
				}
			}
			return $logs;
		}
		return [];
	}

	/**
	 * Search for all battles where a specific entity is engaged.
	 *
	 * @return array<Battle>
	 */
	public function findFor(Identifiable $entity): array {
		$id = $entity->Id()->Id();
		if (isset($this->party[$id])) {
			$logs = [];
			foreach ($this->party[$id] as $battle) {
				$logs[] = $this->logs[$battle];
			}
			return $logs;
		}
		return [];
	}

	/**
	 * Add a Battle to persistence.
	 */
	public function add(Battle $battle): static {
		$id = count($this->logs);
		$this->logs[] = $battle;
		$regionId = $battle->Location()->Id()->Id();
		foreach ($battle->Participants() as $party) {
			$partyId                                = $party->Id()->Id();
			$this->regionParty[$regionId][$partyId] = $id;
			if ($party instanceof DisguisedParty) {
				$partyId = $party->Real()->Id()->Id();
			}
			$this->party[$partyId][] = $id;

		}
		return $this;
	}

	/**
	 * Delete all battles as preparation for a new turn.
	 */
	public function clear(): static {
		$this->logs        = [];
		$this->party       = [];
		$this->regionParty = [];
		return $this;
	}

	/**
	 * Load battles.
	 */
	public function load(): static {
		if (!$this->isLoaded) {
			foreach (Lemuria::Game()->getHostilities() as $battle) {
				$log = new BattleLog();
				$log->unserialize($battle);
				$this->add($log);
			}
			$this->isLoaded = true;
		}
		return $this;
	}

	/**
	 * Save battles.
	 */
	public function save(): static {
		$battles = [];
		foreach ($this->logs as $log) {
			$battles[] = $log->serialize();
		}
		Lemuria::Game()->setHostilities($battles);
		return $this;
	}
}
