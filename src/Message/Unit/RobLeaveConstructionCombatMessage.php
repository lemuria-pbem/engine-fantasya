<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class RobLeaveConstructionCombatMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Event;

	protected Section $section = Section::Battle;

	protected Id $place;

	protected function create(): string {
		return 'Unit ' . $this->id . ' leaves the construction ' . $this->place . ' for the robbery.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->place = $message->get();
	}
}
