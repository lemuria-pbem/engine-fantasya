<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;

use Lemuria\Model\Catalog;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Region;

abstract class AbstractRegionEffect extends AbstractEffect
{
	private ?Region $region = null;

	#[ExpectedValues(valuesFromClass: Catalog::class)]
	#[Pure]
	public function Catalog(): Domain {
		return Domain::LOCATION;
	}

	public function Region(): Region {
		if (!$this->region) {
			$this->region = Region::get($this->Id());
		}
		return $this->region;
	}

	public function setRegion(Region $region): self {
		$this->region = $region;
		$this->setId($region->Id());
		return $this;
	}
}
