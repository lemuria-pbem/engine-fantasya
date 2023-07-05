<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception;

use Lemuria\Engine\Fantasya\Message\Exception;
use Lemuria\Model\Dictionary;

/**
 * This exception is thrown when executing a Command fails.
 */
class CommandException extends ActionException
{
	protected Exception $translationKey = Exception::None;

	public function getTranslation(): string {
		$dictionary = new Dictionary();
		if ($dictionary->has('exception', $this->translationKey->name)) {
			return $this->translate($dictionary->get('exception', $this->translationKey->name));
		}
		return $this->getFallbackTranslation();
	}

	protected function translate(string $template): string {
		return $template;
	}

	protected function getFallbackTranslation(): string {
		return $this->getMessage();
	}
}
