<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AnnouncementUnitMessage extends AnnouncementAnonymousMessage
{
	protected function create(): string {
		return 'Unit ' . $this->recipient . ' has received a message from unit ' . $this->sender . ': "' . $this->message . '"';
	}
}
