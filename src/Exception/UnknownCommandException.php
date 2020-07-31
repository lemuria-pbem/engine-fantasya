<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Exception;

use Lemuria\Engine\Command;

/**
 * This exception is thrown when an unknown Command is parsed.
 */
class UnknownCommandException extends CommandException
{
	/**
	 * Create exception.
	 *
	 * @param string|Command $command
	 * @param CommandException $exception
	 */
	public function __construct($command = null, CommandException $exception = null) {
		$message = 'Unknown command' . ($command ? ': "' . $command . '"' : '.');
		parent::__construct($message, 0, $exception);
	}
}
