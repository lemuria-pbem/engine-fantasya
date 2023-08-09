<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception;

use function Lemuria\mbUcFirst;
use Lemuria\Singleton;

/**
 * This exception is thrown when an unknown item is parsed.
 */
class UnknownItemException extends UnknownArgumentException
{
	public function __construct(Singleton|string $item, ?CommandException $exception = null) {
		if (is_string($item)) {
			$item = mbUcFirst($item);
		}
		parent::__construct($item, 'Unknown item ' . parent::PLACEHOLDER . '.', $exception);
	}
}
