<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Construction;

abstract class AbstractConstructionEffect extends AbstractEffect
{
	private ?Construction $construction = null;

	public function Catalog(): Domain {
		return Domain::Construction;
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
