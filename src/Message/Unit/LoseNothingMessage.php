<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class LoseNothingMessage extends LoseAllMessage
{
	protected Result $result = Result::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has no ' . $this->commodity . ' to dismiss.';
	}
}
