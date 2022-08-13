<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Continent;

abstract class AbstractContinentEffect extends AbstractEffect
{
	private ?Continent $continent = null;

	public function Catalog(): Domain {
		return Domain::CONTINENT;
	}

	public function Continent(): Continent {
		if (!$this->continent) {
			$this->continent = Continent::get($this->Id());
		}
		return $this->continent;
	}

	public function setContinent(Continent $continent): self {
		$this->continent = $continent;
		$this->setId($continent->Id());
		return $this;
	}
}
