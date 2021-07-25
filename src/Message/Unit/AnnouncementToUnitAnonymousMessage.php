<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AnnouncementToUnitAnonymousMessage extends AnnouncementToUnitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has sent an anonymous message to unit ' . $this->target . ': "' . $this->message . '"';
	}
}
