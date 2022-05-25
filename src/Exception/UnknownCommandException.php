<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception;

use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Phrase;

/**
 * This exception is thrown when an unknown Command is parsed.
 */
class UnknownCommandException extends CommandException
{
	public function __construct(Command|Phrase|string|null $command = null, ?CommandException $exception = null) {
		$message = 'Unknown command' . ($command ? ' ' . $command : '.');
		parent::__construct($message, 0, $exception);
	}
}
