<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

class SmashDestroyRoadMessage extends SmashNoRoadToMessage
{
	protected string $level = Message::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has destroyed the road to ' . $this->direction . ' in region ' . $this->region . '.';
	}
}
