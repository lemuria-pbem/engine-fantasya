<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use Lemuria\Model\Lemuria\Intelligence;
use Lemuria\Model\Lemuria\Region;

final class State
{
	private static ?self $instance = null;

	public static function getInstance(): State {
		if (self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @var array(int=>Availability)
	 */
	private array $availability = [];

	/**
	 * @var array(int=>Allocation)
	 */
	private array $allocation = [];

	/**
	 * @var array(int=>Intelligence)
	 */
	private array $intelligence = [];

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
	 * Get a region's intelligence.
	 */
	public function getIntelligence(Region $region): Intelligence {
		$id = $region->Id()->Id();
		if (!isset($this->intelligence[$id])) {
			$this->intelligence[$id] = new Intelligence($region);
		}
		return $this->intelligence[$id];
	}
}
