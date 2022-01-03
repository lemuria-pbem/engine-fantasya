<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Combat\Campaign;
use Lemuria\Engine\Fantasya\Event\Behaviour;
use Lemuria\Engine\Fantasya\Factory\DirectionList;
use Lemuria\Engine\Fantasya\Factory\Workload;
use Lemuria\Id;
use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Fantasya\Intelligence;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Reassignment;

final class State implements Reassignment
{
	private static ?self $instance = null;

	public bool $isTravelling = false;

	private LemuriaTurn $turn;

	private ?TurnOptions $turnOptions = null;

	private ?Casts $casts = null;

	/**
	 * @var array(int=>Availability)
	 */
	private array $availability = [];

	/**
	 * @var array(int=>Allocation)
	 */
	private array $allocation = [];

	/**
	 * @var array(int=>Commerce)
	 */
	private array $commerce = [];

	/**
	 * @var array(int=>Intelligence)
	 */
	private array $intelligence = [];

	/**
	 * @var array(int=>ActivityProtocol)
	 */
	private array $protocol = [];

	/**
	 * @var array(int=>Workload)
	 */
	private array $workload = [];

	/**
	 * @var array(int=>DirectionList)
	 */
	private array $travelRoute = [];

	/**
	 * @var array(int=>Campaign)
	 */
	private array $campaigns = [];

	/**
	 * @var Behaviour[]
	 */
	private array $monsters = [];

	public static function getInstance(LemuriaTurn $turn = null): State {
		if (!self::$instance) {
			self::$instance = new self();
			Lemuria::Catalog()->addReassignment(self::$instance);
		}
		if ($turn) {
			self::$instance->turn = $turn;
		}
		return self::$instance;
	}

	public function reassign(Id $oldId, Identifiable $identifiable): void {
		$old = $oldId->Id();
		$new = $identifiable->Id()->Id();
		switch ($identifiable->Catalog()) {
			case Catalog::UNITS :
				$this->protocol[$new] = $this->protocol[$old];
				unset($this->protocol[$old]);
				if (isset($this->travelRoute[$old])) {
					$this->travelRoute[$new] = $this->travelRoute[$old];
					unset($this->travelRoute[$old]);
				}
				if (isset($this->workload[$old])) {
					$this->workload[$new] = $this->workload[$old];
					unset($this->workload[$old]);
				}
				break;
			case Catalog::LOCATIONS :
				$this->allocation[$new] = $this->allocation[$old];
				unset($this->allocation[$old]);
				$this->availability[$new] = $this->availability[$old];
				unset($this->availability[$new]);
				$this->campaigns[$new] = $this->campaigns[$old];
				unset($this->campaigns[$old]);
				$this->commerce[$new] = $this->commerce[$old];
				unset($this->commerce[$old]);
				$this->intelligence[$new] = $this->intelligence[$old];
				unset($this->intelligence[$old]);
				break;
		}
	}

	public function remove(Identifiable $identifiable): void {
		$old = $identifiable->Id()->Id();
		switch ($identifiable->Catalog()) {
			case Catalog::UNITS :
				unset($this->protocol[$old]);
				unset($this->travelRoute[$old]);
				unset($this->workload[$old]);
				break;
			case Catalog::LOCATIONS :
				unset($this->allocation[$old]);
				unset($this->availability[$old]);
				unset($this->campaigns[$old]);
				unset($this->commerce[$old]);
				unset($this->intelligence[$old]);
				break;
		}
	}

	public function getTurnOptions(): TurnOptions {
		if (!$this->turnOptions) {
			$this->turnOptions = new TurnOptions();
		}
		return $this->turnOptions;
	}

	public function getCasts(): Casts {
		if (!$this->casts) {
			$this->casts = new Casts();
		}
		return $this->casts;
	}

	/**
	 * Get a region's available resources.
	 */
	public function getAvailability(Region $region): Availability {
		$id = $region->Id()->Id();
		if (!isset($this->availability[$id])) {
			$this->availability[$id] = new Availability($region);
		}
		return $this->availability[$id];
	}

	/**
	 * Get a region's allocation.
	 */
	public function getAllocation(Region $region): Allocation {
		$id = $region->Id()->Id();
		if (!isset($this->allocation[$id])) {
			$this->allocation[$id] = new Allocation($this->getAvailability($region));
		}
		return $this->allocation[$id];
	}

	/**
	 * Get a region's commerce.
	 */
	public function getCommerce(Region $region): Commerce {
		$id = $region->Id()->Id();
		if (!isset($this->commerce[$id])) {
			$this->commerce[$id] = new Commerce($region);
		}
		return $this->commerce[$id];
	}

	/**
	 * Get a region's intelligence.
	 */
	public function getIntelligence(Region $region): Intelligence {
		$id = $region->Id()->Id();
		if (!isset($this->intelligence[$id])) {
			$this->intelligence[$id] = new Intelligence($region);
		}
		return $this->intelligence[$id];
	}

	/**
	 * Get a unit's activity protocol.
	 */
	public function getProtocol(Unit $unit): ActivityProtocol {
		$id = $unit->Id()->Id();
		if (!isset($this->protocol[$id])) {
			$this->protocol[$id] = new ActivityProtocol($unit);
		}
		return $this->protocol[$id];
	}

	/**
	 * Get a unit's workload.
	 */
	public function getWorkload(Unit $unit): Workload {
		$id = $unit->Id()->Id();
		if (!isset($this->workload[$id])) {
			$this->workload[$id] = new Workload();
		}
		return $this->workload[$id];
	}

	/**
	 * Get the travel route of a unit.
	 */
	#[Pure] public function getTravelRoute(Unit $unit): ?DirectionList {
		$id = $unit->Id()->Id();
		return $this->travelRoute[$id] ?? null;
	}

	/**
	 * Get the battle of a region.
	 */
	public function getCampaign(Region $region): Campaign {
		$id = $region->Id()->Id();
		if (!isset($this->campaigns[$id])) {
			$this->campaigns[$id] = new Campaign($region);
		}
		return $this->campaigns[$id];
	}

	/**
	 * @return Commerce[]
	 */
	public function getAllCommerces(): array {
		return array_values($this->commerce);
	}

	/**
	 * @return Behaviour[]
	 */
	public function getAllMonsters(): array {
		return $this->monsters;
	}

	public function setTurnOptions(TurnOptions $options): void {
		$this->turnOptions = $options;
	}

	public function setTravelRoute(Unit $unit, DirectionList $travelRoute): void {
		$id = $unit->Id()->Id();
		$this->travelRoute[$id] = $travelRoute;
	}

	public function addMonster(Behaviour $monster): void {
		$this->monsters[] = $monster;
	}

	public function injectIntoTurn(Action $action): void {
		$this->turn->inject($action);
	}
}
