<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Announcement;
use Lemuria\Engine\Fantasya\Message\AnnouncementTrait;

class AnnouncementAnonymousMessage extends AbstractUnitMessage implements Announcement
{
	use AnnouncementTrait;

	protected function create(): string {
		return 'Unit ' . $this->recipient . ' has received an anonymous message : "' . $this->message . '"';
	}
}
