<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\Announcement;
use Lemuria\Engine\Fantasya\Message\AnnouncementTrait;

class AnnouncementPartyMessage extends AbstractPartyMessage implements Announcement
{
	use AnnouncementTrait;

	protected function create(): string {
		return 'We received a message from party ' . $this->sender . ': "' . $this->message . '"';
	}
}
