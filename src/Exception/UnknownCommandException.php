<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception;

use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Phrase;

/**
 * This exception is thrown when an unknown Command is parsed.
 */
class UnknownCommandException extends UnknownArgumentException
{
	public function __construct(Command|Phrase|string|null $command = null, ?CommandException $exception = null) {
		parent::__construct($command, 'Unknown command ' . parent::PLACEHOLDER . '.', $exception);
	}
}
