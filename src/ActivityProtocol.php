<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Command\UnitCommand;
use Lemuria\Lemuria;
use Lemuria\Model\Lemuria\Unit;

/**
 * A protocol of units' activities.
 */
final class ActivityProtocol
{
	private bool $hasActivity = false;

	private bool $hasDefault = false;

	/**
	 * Create new activity protocol for a unit.
	 */
	#[Pure] public function __construct(private Unit $unit) {
	}

	/**
	 * Check if unit has an activity already.
	 */
	#[Pure] public function hasActivity(): bool {
		return $this->hasActivity;
	}

	/**
	 * Add a command to the protocol.
	 */
	public function commit(UnitCommand $command): bool {
		Lemuria::Orders()->getCurrent($this->unit->Id())[] = $command->Phrase();
		if ($command instanceof Activity) {
			if ($this->hasActivity) {
				return false;
			}
			$this->hasActivity = true;
			if (!$this->hasDefault) {
				$default = $command->getNewDefault();
				if ($default) {
					$this->addDefault($default);
				}
			}
		}
		return true;
	}

	/**
	 * Add a command to the default orders.
	 */
	public function addDefault(UnitCommand $command): void {
		if ($command instanceof Activity) {
			$this->hasDefault = true;
		}
		Lemuria::Orders()->getDefault($this->unit->Id())[] = $command->Phrase();
	}
}
