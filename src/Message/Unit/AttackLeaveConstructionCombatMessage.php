<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class AttackLeaveConstructionCombatMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Event;

	protected Section $section = Section::Battle;

	protected Id $place;

	protected function create(): string {
		return 'Unit ' . $this->id . ' leaves the construction ' . $this->place . ' for battle.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->place = $message->get();
	}
}
