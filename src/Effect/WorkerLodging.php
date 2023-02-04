<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Unit;

final class WorkerLodging extends AbstractConstructionEffect
{
	private People $bookings;

	/**
	 * @var array<int, bool>
	 */
	private array $hasSpace = [];

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->bookings = new People();
	}

	public function Space(): int {
		return max(0, $this->Construction()->getFreeSpace() - $this->bookings->Size());
	}

	public function hasBooked(Unit $unit): bool {
		return $this->bookings->has($unit->Id());
	}

	public function hasSpace(Unit $unit): bool {
		$id = $unit->Id()->Id();
		if (array_key_exists($id, $this->hasSpace)) {
			return $this->hasSpace[$id];
		}
		return false;
	}

	public function book(Unit $unit): WorkerLodging {
		$id                  = $unit->Id()->Id();
		$free                = $this->Construction()->getFreeSpace();
		$this->hasSpace[$id] = $free >= $unit->Size();
		$this->bookings->add($unit);
		return $this;
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
