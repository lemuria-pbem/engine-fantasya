<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Turn;

class Options
{
	private CherryPicker $cherryPicker;

	private bool $debugBattles = false;

	private bool $isSimulation = false;

	private bool $throwExceptions = false;

	public function __construct() {
		$this->cherryPicker = new DefaultCherryPicker();
	}

	public function CherryPicker(): CherryPicker {
		return $this->cherryPicker;
	}

	public function DebugBattles(): bool {
		return $this->debugBattles;
	}

	public function IsSimulation(): bool {
		return $this->isSimulation;
	}

	public function ThrowExceptions(): bool {
		return $this->throwExceptions;
	}

	public function setCherryPicker(CherryPicker $cherryPicker): Options {
		$this->cherryPicker = $cherryPicker;
		return $this;
	}

	public function setDebugBattles(bool $debugBattles): Options {
		$this->debugBattles = $debugBattles;
		return $this;
	}

	public function setIsSimulation(bool $isSimulation): Options {
		$this->isSimulation = $isSimulation;
		return $this;
	}

	public function setThrowExceptions(bool $throwExceptions): Options {
		$this->throwExceptions = $throwExceptions;
		return $this;
	}
}
