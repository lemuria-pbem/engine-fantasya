<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

class SmashLeaveConstructionMessage extends SmashNotConstructionMessageOwnerMessage
{
	protected string $level = Message::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has left the construction ' . $this->construction . ' before it is destroyed.';
	}
}
