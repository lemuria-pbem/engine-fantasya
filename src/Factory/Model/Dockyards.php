<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Factory\PortsTrait;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Ship;
use Lemuria\Model\Fantasya\Unit;

class Dockyards
{
	use MessageTrait;
	use PortsTrait;

	public function __construct(protected readonly Ship $ship, Unit $unit) {
		$this->init($unit, $unit->Region());
	}

	public function CanBuildHere(): bool {
		return $this->canBeSailedTo($this->ship);
	}

	#[Pure] public function Port(): ?Construction {
		$size = $this->ship->Captain();
		foreach ($this->friendly as $port) {
			if ($this->hasSpace($port, $size)) {
				return $port;
			}
		}
		foreach ($this->allied as $port) {
			if ($this->hasSpace($port, $size)) {
				return $port;
			}
		}
		foreach ($this->unmaintained as $port) {
			if ($this->hasSpace($port, $size)) {
				return $port;
			}
		}
		foreach ($this->unguarded as $port) {
			if ($this->hasSpace($port, $size)) {
				return $port;
			}
		}
		return null;
	}
}
