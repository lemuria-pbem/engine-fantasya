<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use Lemuria\Engine\Lemuria\Command\UnitCommand;

/**
 * Activities are commands that keep a unit busy the whole round, so it cannot do more than one activity per round.
 */
interface Activity
{
	/**
	 * Get the new default command.
	 */
	public function getNewDefault(): ?UnitCommand;
}
