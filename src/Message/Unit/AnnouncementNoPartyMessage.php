<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AnnouncementNoPartyMessage extends AnnouncementNoUnitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot find party ' . $this->target . ' to send a message.';
	}
}
