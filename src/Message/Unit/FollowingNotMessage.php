<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class FollowingNotMessage extends FollowingMessage
{
	protected function create(): string {
		return 'We will not follow unit ' . $this->leader . ' any longer.';
	}
}
