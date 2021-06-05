<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class FightMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected int $section = Section::BATTLE;

	protected int $position;

	protected function create(): string {
		return 'Unit ' . $this->id . ' will fight at position ' . $this->position . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->position = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'position') {
			$position = $this->translateKey('combat.battleRow.position_' . $this->position);
			if ($position) {
				return $position;
			}
		}
		return parent::getTranslation($name);
	}
}
