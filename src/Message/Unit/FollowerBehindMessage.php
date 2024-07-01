<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Reliability;

class FollowerBehindMessage extends FollowerStoppedMessage
{
	protected Reliability $reliability = Reliability::Determined;

	protected function create(): string {
		return 'Our follower ' . $this->follower . ' could not keep up with us and is left behind in region ' . $this->region . '.';
	}
}
