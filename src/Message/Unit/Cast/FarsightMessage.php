<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Message;

class FarsightMessage extends FarsightUnknownMessage
{
	protected string $level = Message::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' reveals everything that is going on in region ' . $this->region . '.';
	}
}
