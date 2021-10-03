<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

final class WorkerLodging extends AbstractConstructionEffect
{
	private int $booking = 0;

	public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
	}

	public function Booking(): int {
		return $this->booking;
	}

	public function book(int $places): WorkerLodging {
		$this->booking += $places;
		return $this;
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
