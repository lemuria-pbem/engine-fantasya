<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception;

use JetBrains\PhpStorm\Pure;

use Lemuria\Singleton;

/**
 * This exception is thrown when an unknown item is parsed.
 */
class UnknownItemException extends CommandException
{
	#[Pure] public function __construct(Singleton|string $item, ?CommandException $exception = null) {
		$message = 'Unknown item ' . $item;
		parent::__construct($message, 0, $exception);
	}
}
