<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Exception;

use Lemuria\Engine\Lemuria\Command\UnitCommand;

/**
 * This exception is thrown when a unit tries to execute more than one activity.
 */
class ActivityException extends CommandException
{
	/**
	 * Create an exception for an activity.
	 *
	 * @param UnitCommand $command
	 */
	public function __construct(UnitCommand $command) {
		parent::__construct('Unit ' . $command->getUnit()->Id() . ' cannot have more than one activity.');
	}
}
