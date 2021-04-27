<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class FollowNoMoveMessage extends FollowMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' follows unit ' . $this->leader . ' which has not moved.';
	}
}
