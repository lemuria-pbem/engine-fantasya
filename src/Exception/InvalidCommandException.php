<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Exception;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Command;

/**
 * This exception is thrown when a Command has invalid parameters.
 */
class InvalidCommandException extends CommandException
{
	#[Pure] public function __construct(Command|string $command, ?string $explanation = null, ?\Throwable $previous = null) {
		$message = 'Error in command "' . $command . '"' . $explanation ? ': ' . $explanation : '.';
		parent::__construct($message, 0, $previous);
	}
}
