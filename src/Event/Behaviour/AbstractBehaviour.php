<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour;

use Lemuria\Engine\Fantasya\Event\Behaviour;
use Lemuria\Model\Fantasya\Unit;

abstract class AbstractBehaviour implements Behaviour
{
	protected Unit $unit;

	public function Unit(): Unit {
		return $this->unit;
	}

	public function setUnit(Unit $unit): Behaviour {
		$this->unit = $unit;
		return $this;
	}
}
