<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Exception;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Exception\CommandException;

/**
 * This exception is thrown when an invalid reference to a TEMP Unicum is found.
 */
class TempUnicumException extends CommandException
{
	#[Pure] public function __construct(string $message, ?CommandException $commandException = null) {
		parent::__construct($message, 0, $commandException);
	}
}
