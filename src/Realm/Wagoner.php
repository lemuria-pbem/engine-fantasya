<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Realm;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Travel\Trip;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Unit;

class Wagoner
{
	protected Trip $trip;

	private int $incoming;

	private int $outgoing;

	public function __construct(protected readonly Unit $unit) {
		$calculus       = new Calculus($this->unit);
		$this->trip     = $calculus->getTrip();
		$this->incoming = $this->trip->Capacity();
		$this->outgoing = $this->incoming;
	}

	public function Unit(): Unit {
		return $this->unit;
	}

	public function Incoming(): int {
		return $this->incoming;
	}

	public function Outgoing(): int {
		return $this->outgoing;
	}

	public function UsedCapacity(): float {
		$used = max($this->incoming, $this->outgoing) / $this->trip->Capacity();
		if ($used < 0.0 || $used > 1.0) {
			throw new LemuriaException('Used capacity of wagoner ' . $this->unit . ' out of range: ' . $used);
		}
		return $used;
	}

	public function fetch(int $incoming): int {
		$this->incoming -= $incoming;
		return $this->incoming;
	}

	public function send(int $outgoing): int {
		$this->outgoing -= $outgoing;
		return $this->outgoing;
	}
}
