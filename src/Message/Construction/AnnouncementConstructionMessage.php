<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Fantasya\Message\Announcement;
use Lemuria\Engine\Fantasya\Message\AnnouncementTrait;

class AnnouncementConstructionMessage extends AbstractConstructionMessage implements Announcement
{
	use AnnouncementTrait;

	protected function create(): string {
		return 'Message from party ' . $this->sender . ': "' . $this->message . '"';
	}
}
