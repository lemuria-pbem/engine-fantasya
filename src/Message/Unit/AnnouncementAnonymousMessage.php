<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class AnnouncementAnonymousMessage extends AbstractUnitMessage
{
	protected string $level = Message::EVENT;

	protected string $message;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has received an anonymous message : "' . $this->message . '"';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->message = $message->getParameter();
	}
}
