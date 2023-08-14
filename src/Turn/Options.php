<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Turn;

use Lemuria\Engine\Fantasya\Turn\Option\ThrowOption;

class Options
{
	private CherryPicker $cherryPicker;

	private bool $debugBattles = false;

	private bool $isSimulation = false;

	private bool $isProfiling = false;

	private ThrowOption $throwExceptions;

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

	public function IsProfiling(): bool {
		return $this->isProfiling;
	}

	public function ThrowExceptions(): ThrowOption {
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

	public function setIsProfiling(bool $isProfiling): Options {
		$this->isProfiling = $isProfiling;
		return $this;
	}

	public function setThrowExceptions(ThrowOption $throwExceptions): Options {
		$this->throwExceptions = $throwExceptions;
		return $this;
	}
}
