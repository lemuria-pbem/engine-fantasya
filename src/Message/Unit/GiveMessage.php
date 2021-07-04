<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Message;

class GiveMessage extends GiveRejectedMessage
{
	protected string $level = Message::SUCCESS;

	#[Pure] protected function create(): string {
		return 'Unit ' . $this->id . ' gives ' . $this->gift . ' to unit ' . $this->recipient . '.';
	}
}
