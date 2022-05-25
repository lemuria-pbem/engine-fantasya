<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Exception;

use Lemuria\Engine\Fantasya\Exception\CommandException;

/**
 * This exception is thrown when an invalid reference to a TEMP Unit is found.
 */
class TempUnitException extends CommandException
{
	public function __construct(string $message, ?CommandException $commandException = null) {
		parent::__construct($message, 0, $commandException);
	}
}
