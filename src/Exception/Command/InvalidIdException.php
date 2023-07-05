<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception\Command;

use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Message\Exception;

class InvalidIdException extends CommandException
{
	private readonly string $id;

	public function __construct(string $id, \Throwable $previous = null) {
		$this->id = $this->cleanId($id);
		parent::__construct("'" . $this->id . "' is not a valid ID.", previous: $previous);
		$this->translationKey = Exception::InvalidId;
	}

	protected function translate(string $template): string {
		return str_replace('$id', $this->id, $template);
	}

	private function cleanId(string $id): string {
		return trim(strip_tags($id));
	}
}
