<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception;

use Lemuria\Engine\Fantasya\Message\Exception;
use Lemuria\Engine\Fantasya\Phrase;

/**
 * This exception is thrown when VORLAGE comes with an invalid command.
 */
class InvalidDefaultException extends UnknownArgumentException
{
	public function __construct(Phrase $command) {
		parent::__construct((string)$command, 'Order ' . parent::PLACEHOLDER . ' in default command is invalid.');
		$this->translationKey = Exception::InvalidDefault;
	}

	protected function translate(string $template): string {
		return str_replace(self::PLACEHOLDER, $this->getArgument(), $template);
	}
}
