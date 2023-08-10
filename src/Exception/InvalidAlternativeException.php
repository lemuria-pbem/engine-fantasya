<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception;

use Lemuria\Engine\Fantasya\Message\Exception;
use Lemuria\Engine\Fantasya\Phrase;

/**
 * This exception is thrown when ALTERNATIVE comes with a non-activity command.
 */
class InvalidAlternativeException extends CommandException
{
	public function __construct(protected Phrase $command) {
		parent::__construct('Only activities can be executed alternatively.');
		$this->translationKey = Exception::InvalidAlternative;
	}
}
