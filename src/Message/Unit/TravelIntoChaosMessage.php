<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class TravelIntoChaosMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected string $direction;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot move ' . $this->direction . ' into the Chaos.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->direction = $message->getParameter();
	}
}
