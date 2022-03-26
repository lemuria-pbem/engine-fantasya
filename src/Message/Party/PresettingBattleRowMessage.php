<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class PresettingBattleRowMessage extends AbstractPartyMessage
{
	protected string $level = Message::SUCCESS;

	protected Section $section = Section::BATTLE;

	protected int $position;

	protected function create(): string {
		return 'The default battle row for new recruits has been set to "' . $this->position . '".';
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
