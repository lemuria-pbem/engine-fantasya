<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Effect;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Score;
use Lemuria\Model\Catalog;
use Lemuria\Model\Lemuria\Unit;

abstract class AbstractUnitEffect extends AbstractEffect
{
	private ?Unit $unit = null;

	#[ExpectedValues(valuesFromClass: Catalog::class)]
	#[Pure]
	public function Catalog(): int {
		return Score::UNIT;
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
