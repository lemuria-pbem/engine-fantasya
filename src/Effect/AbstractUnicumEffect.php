<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;

use Lemuria\Model\Catalog;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Unicum;

abstract class AbstractUnicumEffect extends AbstractEffect
{
	private ?Unicum $unicum = null;

	#[ExpectedValues(valuesFromClass: Catalog::class)]
	#[Pure]
	public function Catalog(): Domain {
		return Domain::UNICUM;
	}

	public function Unicum(): Unicum {
		if (!$this->unicum) {
			$this->unicum = Unicum::get($this->Id());
		}
		return $this->unicum;
	}

	public function setUnicum(Unicum $unicum): self {
		$this->unicum = $unicum;
		$this->setId($unicum->Id());
		return $this;
	}
}
