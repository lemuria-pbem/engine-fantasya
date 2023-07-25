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

	private int $maximum;

	private int $incoming;

	private int $outgoing;

	public function __construct(protected readonly Unit $unit) {
		$calculus       = new Calculus($this->unit);
		$this->trip     = $calculus->getTrip();
		$this->maximum  = max(0, $this->trip->Capacity() - $this->trip->Weight());
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
		$used = $this->maximum > 0 ? max($this->incoming, $this->outgoing) / $this->maximum : 1.0;
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
