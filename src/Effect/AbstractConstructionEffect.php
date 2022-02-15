<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;

use Lemuria\Model\Catalog;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Construction;

abstract class AbstractConstructionEffect extends AbstractEffect
{
	private ?Construction $construction = null;

	#[ExpectedValues(valuesFromClass: Catalog::class)]
	#[Pure]
	public function Catalog(): Domain {
		return Domain::CONSTRUCTION;
	}

	public function Construction(): Construction {
		if (!$this->construction) {
			$this->construction = Construction::get($this->Id());
		}
		return $this->construction;
	}

	public function setConstruction(Construction $construction): self {
		$this->construction = $construction;
		$this->setId($construction->Id());
		return $this;
	}
}
