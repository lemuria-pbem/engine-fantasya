<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class TravelIntoChaosMessage extends TravelSimulationMessage
{
	protected string $direction;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot move ' . $this->direction . ' into the Chaos.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->direction = $message->getParameter();
	}
}
