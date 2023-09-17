<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception;

use Lemuria\Engine\Fantasya\Message\Exception;
use Lemuria\Engine\Fantasya\Phrase;

/**
 * This exception is thrown when ALTERNATIVE comes with a non-activity command.
 */
class InvalidAlternativeException extends UnknownArgumentException
{
	public function __construct(Phrase $command) {
		parent::__construct((string)$command, parent::PLACEHOLDER . ': Only activities can be executed alternatively.');
		$this->translationKey = Exception::InvalidAlternative;
	}

	protected function translate(string $template): string {
		return str_replace(self::PLACEHOLDER, $this->getArgument(), $template);
	}
}
