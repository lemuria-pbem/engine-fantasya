<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Exception;

use Lemuria\Engine\Lemuria\Command;
use Lemuria\Engine\Lemuria\Exception\CommandException;

/**
 * This exception is thrown when an unknown Command is parsed.
 */
class UnknownCommandException extends CommandException
{
	/**
	 * Create exception.
	 *
	 * @param Command|string|null $command
	 * @param CommandException|null $exception
	 */
	public function __construct($command = null, ?CommandException $exception = null) {
		$message = 'Unknown command' . ($command ? ': "' . $command . '"' : '.');
		parent::__construct($message, 0, $exception);
	}
}
