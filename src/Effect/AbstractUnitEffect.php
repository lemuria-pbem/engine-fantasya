<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Unit;

abstract class AbstractUnitEffect extends AbstractEffect
{
	private ?Unit $unit = null;

	public function Catalog(): Domain {
		return Domain::UNIT;
	}

	public function Unit(): Unit {
		if (!$this->unit) {
			$this->unit = Unit::get($this->Id());
		}
		return $this->unit;
	}

	public function setUnit(Unit $unit): self {
		$this->unit = $unit;
		$this->setId($unit->Id());
		return $this;
	}
}
