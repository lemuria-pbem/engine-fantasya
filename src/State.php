<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Factory\DirectionList;
use Lemuria\Engine\Fantasya\Factory\Workload;
use Lemuria\Exception\LemuriaException;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Intelligence;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;

final class State
{
	private static ?self $instance = null;

	public static function getInstance(): State {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

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
			throw new LemuriaException();
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

	#[Pure] public function getTravelRoute(Unit $unit): ?DirectionList {
		$id = $unit->Id()->Id();
		return $this->travelRoute[$id] ?? null;
	}

	/**
	 * @return Commerce[]
	 */
	public function getAllCommerces(): array {
		return array_values($this->commerce);
	}

	public function setTurnOptions(TurnOptions $options): void {
		$this->turnOptions = $options;
	}

	public function setProtocol(ActivityProtocol $protocol): void {
		$this->protocol[$protocol->Unit()->Id()->Id()] = $protocol;
	}

	public function unsetProtocol(Id $oldId): void {
		unset($this->protocol[$oldId->Id()]);
	}

	public function setTravelRoute(Unit $unit, DirectionList $travelRoute): void {
		$id = $unit->Id()->Id();
		$this->travelRoute[$id] = $travelRoute;
	}
}
