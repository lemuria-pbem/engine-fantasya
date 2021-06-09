<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class AnnouncementNoPartyMessage extends AbstractPartyMessage
{
	protected string $level = Message::FAILURE;

	protected int $section = Section::MAIL;

	protected Id $party;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot find any unit of party ' . $this->party . ' to send a message.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->get();
	}
}
