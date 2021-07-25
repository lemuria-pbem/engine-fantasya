<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AnnouncementNoConstructionMessage extends AnnouncementNoUnitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot find construction ' . $this->target . ' to send a message.';
	}
}
