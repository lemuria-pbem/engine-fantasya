<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception\Command;

use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Message\Exception;
use Lemuria\Id;

class UnitNotFoundException extends CommandException
{
	public function __construct(private readonly Id $id, \Throwable $previous = null) {
		parent::__construct('Unit ' . $id . ' not found.', previous: $previous);
		$this->translationKey = Exception::UnitNotFound;
	}

	protected function translate(string $template): string {
		return str_replace('$id', (string)$this->id, $template);
	}
}
