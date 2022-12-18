<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class NameCaptainMessage extends AbstractVesselMessage
{
	protected string $level = Message::FAILURE;

	protected Id $captain;

	protected function create(): string {
		return "Unit " . $this->captain . " is not a member of the captain's party of vessel " . $this->id . " and thus cannot rename it.";
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->captain = $message->get();
	}
}
