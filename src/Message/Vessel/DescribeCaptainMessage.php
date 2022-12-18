<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class DescribeCaptainMessage extends AbstractVesselMessage
{
	protected string $level = Message::FAILURE;

	protected Id $captain;

	protected function create(): string {
		return "Unit " . $this->captain . " is not in the captain's party of vessel " . $this->id . " and thus cannot describe it.";
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->captain = $message->get();
	}
}
