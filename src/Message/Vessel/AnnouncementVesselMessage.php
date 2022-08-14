<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Fantasya\Message\Announcement;
use Lemuria\Engine\Fantasya\Message\AnnouncementTrait;

class AnnouncementVesselMessage extends AbstractVesselMessage implements Announcement
{
	use AnnouncementTrait;

	protected function create(): string {
		return 'Message from party ' . $this->sender . ': "' . $this->message . '"';
	}
}
