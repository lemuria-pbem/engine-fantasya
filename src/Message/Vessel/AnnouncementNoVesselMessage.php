<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Message;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class AnnouncementNoVesselMessage extends AbstractVesselMessage
{
	protected string $level = Message::FAILURE;

	protected Id $vessel;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot find vessel ' . $this->vessel . ' to send a message.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->vessel = $message->get();
	}
}
