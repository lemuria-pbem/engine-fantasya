<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Announcement;
use Lemuria\Engine\Fantasya\Message\AnnouncementTrait;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class VisitMessage extends AbstractUnitMessage implements Announcement
{
	use AnnouncementTrait;

	protected Result $result = Result::Event;

	protected Section $section = Section::Mail;

	protected function create(): string {
		return 'Unit ' . $this->sender . ' says: „' . $this->message . '“';
	}
}
