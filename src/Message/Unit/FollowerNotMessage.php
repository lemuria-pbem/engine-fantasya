<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class FollowerNotMessage extends FollowerMessage
{
	protected function create(): string {
		return 'Unit ' . $this->follower . ' will not follow us any longer.';
	}
}
