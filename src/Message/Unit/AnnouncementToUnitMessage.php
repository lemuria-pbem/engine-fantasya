<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class AnnouncementToUnitMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected Section $section = Section::MAIL;

	protected Id $target;

	protected string $message;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has sent a message to unit ' . $this->target . ': "' . $this->message . '"';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->target  = $message->get();
		$this->message = $message->getParameter();
	}
}
