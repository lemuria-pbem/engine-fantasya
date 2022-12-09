<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class EnterAlreadyMessage extends EnterMessage
{
	protected Result $result = Result::FAILURE;

	protected function create(): string {
		return 'Unit '. $this->id . ' is already in the construction ' . $this->construction . '.';
	}
}
