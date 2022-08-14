<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\Announcement;
use Lemuria\Engine\Fantasya\Message\AnnouncementTrait;

class AnnouncementRegionMessage extends AbstractRegionMessage implements Announcement
{
	use AnnouncementTrait;

	protected function create(): string {
		return 'Message from party ' . $this->sender . ': "' . $this->message . '"';
	}
}
