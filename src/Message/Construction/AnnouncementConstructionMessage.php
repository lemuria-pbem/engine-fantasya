<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class AnnouncementConstructionMessage extends AbstractConstructionMessage
{
	public const SENDER = 'sender';

	protected string $level = Message::EVENT;

	protected Section $section = Section::MAIL;

	protected string $sender;

	protected string $message;

	protected function create(): string {
		return 'Message from party ' . $this->sender . ': "' . $this->message . '"';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->sender  = $message->getParameter(self::SENDER);
		$this->message = $message->getParameter();
	}
}
