<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception;

/**
 * This exception is thrown when unidentifiable user input is parsed.
 */
class UnknownArgumentException extends CommandException
{
	public final const ARGUMENT = 'argument';

	protected final const PLACEHOLDER = '{' . self::ARGUMENT . '}';

	public function __construct(
		private readonly \Stringable|string|null $argument = null,
		?string $message = 'Unknown argument ' . self::PLACEHOLDER . '.',
		?CommandException $exception = null
	) {
		parent::__construct($message, 0, $exception);
	}

	public function getArgument(): \Stringable|string|null {
		return $this->argument;
	}

	protected function getFallbackTranslation(): string {
		$argument = trim(strip_tags($this->argument));
		return str_replace(self::PLACEHOLDER, $argument, $this->getMessage());
	}
}
