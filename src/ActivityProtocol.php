<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use Lemuria\Engine\Lemuria\Command\UnitCommand;
use Lemuria\Model\Lemuria\Unit;

/**
 * A protocol of units' activities.
 */
final class ActivityProtocol
{
	private Unit $unit;

	private bool $hasActivity = false;

	/**
	 * Create new activity protocol for a unit.
	 *
	 * @param Unit $unit
	 */
	public function __construct(Unit $unit) {
		$this->unit = $unit;
	}

	/**
	 * Check if unit has an activity already.
	 *
	 * @return bool
	 */
	public function hasActivity(): bool {
		return $this->hasActivity;
	}

	/**
	 * Add a command to the protocol.
	 *
	 * @param UnitCommand $command
	 * @return bool
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
