<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;

class EnterAlreadyMessage extends EnterMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit '. $this->id . ' is already in the construction ' . $this->construction . '.';
	}
}
