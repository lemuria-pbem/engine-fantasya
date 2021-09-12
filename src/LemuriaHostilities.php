<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Combat\Battle;
use Lemuria\Engine\Fantasya\Combat\BattleLog;
use Lemuria\Engine\Hostilities;
use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\Model\Location;

class LemuriaHostilities implements Hostilities
{
	/**
	 * @var array(int=>array)
	 */
	private array $regionParty = [];

	/**
	 * @var BattleLog[]
	 */
	private array $logs = [];

	private bool $isLoaded = false;

	/**
	 * Search for the battle in a location where a specific entity is engaged.
	 */
	#[Pure] public function find(Location $location, Identifiable $entity): ?Battle {
		return $this->regionParty[$location->Id()->Id()][$entity->Id()->Id()] ?? null;
	}

	/**
	 * Search for all battles in a location.
	 *
	 * @return Battle[]
	 */
	#[Pure] public function findAll(Location $location): array {
		$id = $location->Id()->Id();
		if (isset($this->regionParty[$id])) {
			return array_values($this->regionParty[$id]);
		}
		return [];
	}

	/**
	 * Add a Battle to persistence.
	 */
	public function add(Battle $battle): Hostilities {
		$id = count($this->logs);
		$this->logs[] = $battle;
		$regionId = $battle->Location()->Id()->Id();
		foreach ($battle->Participants() as $party) {
			$partyId = $party->Id()->Id();
			$this->regionParty[$regionId][$partyId] = $id;
		}
		return $this;
	}

	/**
	 * Load battles.
	 */
	public function load(): Hostilities {
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
	public function save(): Hostilities {
		$battles = [];
		foreach ($this->logs as $log) {
			$battles[] = $log->serialize();
		}
		Lemuria::Game()->setHostilities($battles);
		return $this;
	}
}
