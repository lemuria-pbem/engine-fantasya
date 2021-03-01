<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Effect;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Score;
use Lemuria\Model\Catalog;
use Lemuria\Model\Lemuria\Vessel;

abstract class AbstractVesselEffect extends AbstractEffect
{
	private ?Vessel $vessel = null;

	#[ExpectedValues(valuesFromClass: Catalog::class)]
	#[Pure]
	public function Catalog(): int {
		return Score::VESSEL;
	}

	public function Vessel(): Vessel {
		if (!$this->vessel) {
			$this->vessel = Vessel::get($this->Id());
		}
		return $this->vessel;
	}

	public function setVessel(Vessel $vessel): self {
		$this->vessel = $vessel;
		$this->setId($vessel->Id());
		return $this;
	}
}
