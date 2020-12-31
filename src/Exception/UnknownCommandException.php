<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Exception;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Command;

/**
 * This exception is thrown when an unknown Command is parsed.
 */
class UnknownCommandException extends CommandException
{
	#[Pure] public function __construct(Command|string|null $command = null, ?CommandException $exception = null) {
		$message = 'Unknown command' . ($command ? ': "' . $command . '"' : '.');
		parent::__construct($message, 0, $exception);
	}
}
