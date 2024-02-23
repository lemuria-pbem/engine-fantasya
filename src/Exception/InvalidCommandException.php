<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception;

use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Message\Exception;

/**
 * This exception is thrown when a Command has invalid parameters.
 */
class InvalidCommandException extends CommandException
{
	private const PLACEHOLDER = '$command';

	public function __construct(private readonly Command|string $command, ?string $explanation = null, ?\Throwable $previous = null) {
		$message = 'Error in command "' . $command . '"' . ($explanation ? ': ' . $explanation : '.');
		parent::__construct($message, 0, $previous);
		$this->translationKey = Exception::InvalidCommand;
	}

	protected function translate(string $template): string {
		return str_replace(self::PLACEHOLDER, (string)$this->command, $template);
	}
}
