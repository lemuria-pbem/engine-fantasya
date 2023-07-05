<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception;

use Lemuria\Singleton;

/**
 * This exception is thrown when an unknown item is parsed.
 */
class UnknownItemException extends UnknownArgumentException
{
	public function __construct(Singleton|string $item, ?CommandException $exception = null) {
		parent::__construct($item, 'Unknown item ' . parent::PLACEHOLDER . '.', $exception);
	}
}
