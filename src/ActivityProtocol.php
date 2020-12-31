<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Command\UnitCommand;
use Lemuria\Model\Lemuria\Unit;

/**
 * A protocol of units' activities.
 */
final class ActivityProtocol
{
	private bool $hasActivity = false;

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
		if ($command instanceof Activity) {
			if ($this->hasActivity) {
				return false;
			}
			$this->hasActivity = true;
		}
		return true;
	}
}
