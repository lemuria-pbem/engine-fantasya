<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class TravelIntoChaosMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected int $section = Section::MOVEMENT;

	protected string $direction;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot move ' . $this->direction . ' into the Chaos.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->direction = $message->getParameter();
	}
}
