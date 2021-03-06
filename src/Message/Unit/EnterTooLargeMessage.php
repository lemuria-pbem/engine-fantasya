<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class EnterTooLargeMessage extends EnterAlreadyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' is too large for entering the construction ' . $this->construction . '.';
	}
}
