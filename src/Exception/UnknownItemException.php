<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception;

use function Lemuria\mbUcFirst;
use Lemuria\Engine\Fantasya\Message\Exception;
use Lemuria\Singleton;

/**
 * This exception is thrown when an unknown item is parsed.
 */
class UnknownItemException extends UnknownArgumentException
{
	public function __construct(private Singleton|string $item, ?CommandException $exception = null) {
		if (is_string($item)) {
			$item = mbUcFirst($item);
		}
		parent::__construct($item, 'Unknown item ' . parent::PLACEHOLDER . '.', $exception);
		$this->translationKey = Exception::UnknownItem;
	}

	protected function translate(string $template): string {
		return str_replace('$item', $this->item, $template);
	}
}
