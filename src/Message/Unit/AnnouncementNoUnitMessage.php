<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class AnnouncementNoUnitMessage extends AnnouncementOwnMessage
{
	protected Id $target;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot find unit ' . $this->target . ' to send a message.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->target = $message->get();
	}
}
