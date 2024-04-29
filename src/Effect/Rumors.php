<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Factory\Model\Buzzes;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Unit;

class Rumors extends AbstractUnitEffect
{
	private Buzzes $rumors;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->rumors = new Buzzes();
	}

	public function Rumors(): Buzzes {
		return $this->rumors;
	}

	public function getRumorsFor(Unit $unit): Buzzes {
		$buzzes = new Buzzes();
		$i      = 0;
		$party  = $unit->Party();
		$region = $unit->Region();
		foreach ($this->rumors as $rumor) {
			if ($rumor->Origin() !== $party && $rumor->Locality() !== $region) {
				$buzzes[$i++] = $rumor;
			}
		}
		return $buzzes;
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
