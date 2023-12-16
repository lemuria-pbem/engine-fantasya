<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Realm;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Travel\Transport;
use Lemuria\Engine\Fantasya\Travel\Trip;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Animal;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Talent\Riding;
use Lemuria\Model\Fantasya\Unit;

class Wagoner
{
	protected Trip $trip;

	private int $maximum;

	private int $incoming;

	private int $outgoing;

	public function __construct(protected readonly Unit $unit) {
		$calculus      = new Calculus($this->unit);
		$this->trip    = $calculus->getTrip();
		$this->maximum = max(0, $this->trip->Capacity() - $this->trip->Weight());
		if ($this->trip->Speed() <= 1) {
			$this->maximum = (int)round($this->maximum / 2);
		}
		$this->incoming = $this->maximum;
		$this->outgoing = $this->incoming;
	}

	public function Unit(): Unit {
		return $this->unit;
	}

	public function Maximum(): int {
		return $this->maximum;
	}

	public function Incoming(): int {
		return $this->incoming;
	}

	public function Outgoing(): int {
		return $this->outgoing;
	}

	public function UsedCapacity(): float {
		$used = $this->maximum > 0 ? ($this->maximum - min($this->incoming, $this->outgoing)) / $this->maximum : 1.0;
		if ($used < 0.0 || $used > 1.0) {
			throw new LemuriaException('Used capacity of wagoner ' . $this->unit . ' out of range: ' . $used);
		}
		return $used;
	}

	public function getMounts(Animal $animal): int {
		$need     = Transport::requiredRidingLevel($animal);
		$calculus = new Calculus($this->unit);
		$level    = $calculus->knowledge(Riding::class)->Level();
		if ($level < $need) {
			return 0;
		}
		$capacity = (int)floor((1.0 - $this->UsedCapacity()) * $this->unit->Size() * $level);
		return $capacity > $need ? (int)floor($capacity / $need) : 0;
	}

	public function fetch(int $incoming): int {
		$this->incoming -= $incoming;
		return $this->incoming;
	}

	public function send(int $outgoing): int {
		$this->outgoing -= $outgoing;
		return $this->outgoing;
	}

	public function ride(Quantity $animals): Quantity {
		/** @var Animal $animal */
		$animal          = $animals->Commodity();
		$need            = Transport::requiredRidingLevel($animal);
		$calculus        = new Calculus($this->unit);
		$level           = $calculus->knowledge(Riding::class)->Level();
		$size            = $this->unit->Size();
		$capability      = $size * $level;
		$needed          = $animals->Count() * $need;
		$rate            = $needed / $capability;
		$capacity        = (int)ceil($rate * $this->maximum);
		$this->incoming -= $capacity;
		$this->outgoing -= $capacity;
		return $animals;
	}
}
