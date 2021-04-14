<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Message;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class AnnouncementNoConstructionMessage extends AbstractConstructionMessage
{
	protected string $level = Message::FAILURE;

	protected Id $construction;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot find construction ' . $this->construction . ' to send a message.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->construction = $message->get();
	}
}
