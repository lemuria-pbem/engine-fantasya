<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Exception;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Command\UnitCommand;

/**
 * This exception is thrown when a unit tries to execute more than one activity.
 */
class ActivityException extends CommandException
{
	#[Pure] public function __construct(UnitCommand $command) {
		parent::__construct('Unit ' . $command->Unit()->Id() . ' cannot have more than one activity.');
	}
}
