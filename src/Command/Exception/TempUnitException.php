<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Exception;

use Lemuria\Engine\Lemuria\Exception\CommandException;

/**
 * This exception is thrown when an invalid reference to a TEMP Unit is found.
 */
class TempUnitException extends CommandException {

	/**
	 * Create exception.
	 *
	 * @param string $message
	 * @param CommandException|null $commandException
	 */
	public function __construct(string $message, CommandException $commandException = null) {
		parent::__construct($message, 0, $commandException);
	}
}
