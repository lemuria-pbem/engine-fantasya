<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Unicum;

abstract class AbstractUnicumEffect extends AbstractEffect
{
	private ?Unicum $unicum = null;

	public function Catalog(): Domain {
		return Domain::Unicum;
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
