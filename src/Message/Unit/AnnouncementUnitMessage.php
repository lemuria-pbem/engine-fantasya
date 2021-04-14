<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class AnnouncementUnitMessage extends AnnouncementAnonymousMessage
{
	public const SENDER = 'sender';

	protected string $sender;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has received a message from unit ' . $this->sender . ': "' . $this->message . '"';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->sender = $message->getParameter(self::SENDER);
	}
}
