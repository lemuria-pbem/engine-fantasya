<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

class TurnOptions
{
	private bool $isSimulation = false;

	private bool $throwExceptions = false;

	public function IsSimulation(): bool {
		return $this->isSimulation;
	}

	public function ThrowExceptions(): bool {
		return $this->throwExceptions;
	}

	public function setIsSimulation(bool $isSimulation): TurnOptions {
		$this->isSimulation = $isSimulation;
		return $this;
	}

	public function setThrowExceptions(bool $throwExceptions): TurnOptions {
		$this->throwExceptions = $throwExceptions;
		return $this;
	}
}
