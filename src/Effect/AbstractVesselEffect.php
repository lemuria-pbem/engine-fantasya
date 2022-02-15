<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;

use Lemuria\Model\Catalog;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Vessel;

abstract class AbstractVesselEffect extends AbstractEffect
{
	private ?Vessel $vessel = null;

	#[ExpectedValues(valuesFromClass: Catalog::class)]
	#[Pure]
	public function Catalog(): Domain {
		return Domain::VESSEL;
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
