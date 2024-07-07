<?php
/** @noinspection DuplicatedCode */
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Intelligence;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Unit;

/**
 * A resource pool consists of all resources that the units of the same party in a single region carry.
 *
 * Each unit can reserve and consume resources from it.
 */
class ResourcePool
{
	use SiegeTrait;

	protected readonly Party $party;

	protected readonly Region $region;

	protected readonly People $units;

	/**
	 * @var array<int, Resources>
	 */
	protected static array $reservations = [];

	public static function resetReservations(): void {
		self::$reservations = [];
	}

	public function __construct(Unit $unit) {
		$this->party  = $unit->Party();
		$this->region = $unit->Region();
		$intelligence = new Intelligence($this->region);
		$this->units  = new People();
		foreach ($intelligence->getUnits($this->party) as $unit) {
			$this->units->add($unit);
			$id = $unit->Id()->Id();
			if (!isset(self::$reservations[$id])) {
				self::$reservations[$id] = new Resources();
			}
		}
	}

	/**
	 * Take a quantity out of the pool without reserving it.
	 */
	public function take(Unit $unit, Quantity $quantity): Quantity {
		$id = $unit->Id();
		if (!$this->units->has($id)) {
			throw new LemuriaException('Unit ' . $unit . ' is not a pool member.');
		}

		$commodity = $quantity->Commodity();
		$demand    = $quantity->Count();
		if ($demand <= 0) {
			return $quantity;
		}
		$inventory = $unit->Inventory();
		$addition  = new Quantity($commodity, 0);

		foreach ($this->units as $next) {
			if ($next === $unit) {
				continue;
			}
			if ($this->isStoppedBySiege($unit, $next)) {
				continue;
			}

			$nextId           = $next->Id();
			$nextInventory    = $next->Inventory();
			$nextReservations = self::$reservations[$nextId->Id()];
			$nextQuantity     = $nextInventory->offsetGet($commodity);
			$nextCount        = $nextQuantity->Count();
			$nextReserved     = $nextReservations->offsetGet($commodity)->Count();
			$nextAvailable    = $nextCount - $nextReserved;
			if ($nextAvailable <= 0) {
				continue;
			}
			$nextAddition = min($demand, $nextAvailable);
			$demand      -= $nextAddition;
			$nextQuantity = new Quantity($commodity, $nextAddition);
			$addition->add($nextQuantity);
			$nextInventory->remove($nextQuantity);
			Lemuria::Log()->debug('Unit ' . $id . ' takes ' . $nextQuantity . ' from unit ' . $nextId . '.');
			if ($demand <= 0) {
				break;
			}
		}

		if ($addition->Count() <= 0) {
			Lemuria::Log()->debug('Unit ' . $id . ' cannot take any ' . $commodity . ' from other units.');
		} else {
			$inventory->add($addition);
		}
		return $addition;
	}

	/**
	 * Take a quantity out of the pool for the realm fund.
	 */
	public function forFund(Unit $unit, Quantity $quantity): Quantity {
		$id = $unit->Id();
		if (!$this->units->has($id)) {
			throw new LemuriaException('Unit ' . $unit . ' is not a pool member.');
		}

		$commodity    = $quantity->Commodity();
		$demand       = $quantity->Count();
		$inventory    = $unit->Inventory();
		$reservations = self::$reservations[$id->Id()];
		$ownCount     = $inventory->offsetGet($commodity)->Count();
		$reserved     = $reservations->offsetGet($commodity)->Count();
		$available    = $ownCount - $reserved;
		$needed       = $demand - $available;
		if ($needed > 0) {
			$addition   = $this->take($unit, new Quantity($commodity, $needed));
			$available += $addition->Count();
		}
		if ($available < $demand) {
			$demand = $available;
		}
		if ($demand > 0) {
			$inventory->remove(new Quantity($commodity, $demand));
			return new Quantity($commodity, $demand);
		}
		return new Quantity($commodity, 0);
	}

	/**
	 * Make a reservation of one commodity.
	 */
	public function reserve(Unit $unit, Quantity $quantity): Quantity {
		$id = $unit->Id();
		if (!$this->units->has($id)) {
			throw new LemuriaException('Unit ' . $unit . ' is not a pool member.');
		}
		$commodity = $quantity->Commodity();
		$demand    = $quantity->Count();
		if ($demand <= 0) {
			return $quantity;
		}

		$inventory    = $unit->Inventory();
		$reservations = self::$reservations[$id->Id()];
		$ownCount     = $inventory->offsetGet($commodity)->Count();
		$reserved     = $reservations->offsetGet($commodity)->Count();
		$available    = $ownCount - $reserved;
		if ($available >= $demand) {
			$reservations->add($quantity);
			Lemuria::Log()->debug('Unit ' . $id . ' can cover demand of ' . $quantity . '.');
			return $quantity;
		}
		if ($available > 0) {
			$reservation = new Quantity($commodity, $available);
			$reservations->add(new Quantity($commodity, $available));
			$demand -= $available;
			Lemuria::Log()->debug('Unit ' . $id . ' can only cover ' . $reservation . ' of demand.');
		} else {
			$reservation = new Quantity($commodity, 0);
		}
		$addition = new Quantity($commodity, 0);

		foreach ($this->units as $next) {
			if ($next === $unit) {
				continue;
			}
			if ($this->isStoppedBySiege($unit, $next)) {
				continue;
			}

			$nextId           = $next->Id();
			$nextInventory    = $next->Inventory();
			$nextReservations = self::$reservations[$nextId->Id()];
			$nextQuantity     = $nextInventory->offsetGet($commodity);
			$nextCount        = $nextQuantity->Count();
			$nextReserved     = $nextReservations->offsetGet($commodity)->Count();
			$nextAvailable    = $nextCount - $nextReserved;
			if ($nextAvailable <= 0) {
				continue;
			}
			$nextAddition = min($demand, $nextAvailable);
			$demand      -= $nextAddition;
			$nextQuantity = new Quantity($commodity, $nextAddition);
			$addition->add($nextQuantity);
			$nextInventory->remove($nextQuantity);
			Lemuria::Log()->debug('Unit ' . $id . ' receives ' . $nextQuantity . ' from unit ' . $nextId . '.');
			if ($demand <= 0) {
				break;
			}
		}

		if ($addition->Count() <= 0) {
			Lemuria::Log()->debug('Unit ' . $id . ' cannot reserve any ' . $commodity . ' from other units.');
		} else {
			$reservation->add($addition);
			$inventory->add($addition);
			$reservations->add($addition);
			Lemuria::Log()->debug('Unit ' . $id . ' has reserved ' . $reservations->offsetGet($commodity) . ' now.');
		}
		return $reservation;
	}

	/**
	 * Make a reservation of everything that is available in the pool.
	 */
	public function reserveEverything(Unit $unit): static {
		$id = $unit->Id();
		if (!$this->units->has($id)) {
			throw new LemuriaException('Unit ' . $unit . ' is not a pool member.');
		}
		$inventory    = $unit->Inventory();
		$reservations = self::$reservations[$id->Id()];
		$reservations->clear();
		foreach ($inventory as $quantity) {
			$reservations->add($quantity);
		}
		$nextQuantities = new Resources();

		foreach ($this->units as $next) {
			if ($next === $unit) {
				continue;
			}
			if ($this->isStoppedBySiege($unit, $next)) {
				continue;
			}

			$nextId           = $next->Id();
			$nextInventory    = $next->Inventory();
			$nextReservations = self::$reservations[$nextId->Id()];
			$nextQuantities->clear();
			foreach ($nextInventory as $quantity) {
				$commodity = $quantity->Commodity();
				$count     = $quantity->Count();
				$reserved  = $nextReservations->offsetGet($commodity)->Count();
				$available = $count - $reserved;
				if ($available > 0) {
					$quantity = new Quantity($commodity, $available);
					$nextQuantities->add($quantity);
				}
			}
			foreach ($nextQuantities as $quantity) {
				$nextInventory->remove($quantity);
				$inventory->add($quantity);
				$reservations->add($quantity);
				Lemuria::Log()->debug('Unit ' . $unit->Id() . ' reserves ' . $quantity . ' from unit ' . $nextId . '.');
			}
		}

		return $this;
	}
}
