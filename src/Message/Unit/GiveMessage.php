<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;

class GiveMessage extends GiveRejectedMessage
{
	protected string $level = Message::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' gives ' . $this->gift . ' to unit ' . $this->recipient . '.';
	}
}
