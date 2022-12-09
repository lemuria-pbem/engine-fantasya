<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class SmashDestroyRoadMessage extends SmashNoRoadToMessage
{
	protected Result $result = Result::Success;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has destroyed the road to ' . $this->direction . ' in region ' . $this->region . '.';
	}
}
