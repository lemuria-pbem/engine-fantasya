<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Vessel;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;

class TravelOverLandMessage extends TravelShipTooHeavyMessage
{
	protected string $direction;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' cannot move to ' . $this->direction . ' over land.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->direction = $message->getParameter();
	}
}