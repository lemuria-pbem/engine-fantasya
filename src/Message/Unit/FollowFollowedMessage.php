<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class FollowFollowedMessage extends FollowMessage
{
	protected Result $result = Result::Event;

	protected function create(): string {
		return 'Unit ' . $this->leader . ' follows us.';
	}
}
