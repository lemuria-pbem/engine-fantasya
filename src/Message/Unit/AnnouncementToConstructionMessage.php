<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AnnouncementToConstructionMessage extends AnnouncementToUnitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has sent a message to construction ' . $this->target . ': "' . $this->message . '"';
	}
}
