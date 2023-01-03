<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception\Command;

use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Message\Exception;

class InvalidIdException extends CommandException
{
	public function __construct(private readonly string $id, \Throwable $previous = null) {
		parent::__construct("'" . $id . "' is not a valid ID.", previous: $previous);
		$this->translationKey = Exception::InvalidId;
	}

	protected function translate(string $template): string {
		return str_replace('$id', $this->id, $template);
	}
}
