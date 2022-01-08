<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\FightMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FightUnguardMessage;
use Lemuria\Model\Fantasya\Combat;

/**
 * This command is used to set the unit's behaviour in combat.
 *
 * - KÄMPFEN [Aggressiv|Defensiv|Fliehe|Fliehen|Flucht|Hinten|Nicht|Vorn|Vorne]
 */
final class Fight extends UnitCommand
{
	protected function run(): void {
		$n = count($this->phrase);
		if ($n === 0) {
			$position = 'vorne';
		} elseif ($n === 1) {
			$position = strtolower($this->phrase->getParameter());
		} else {
			throw new InvalidCommandException($this);
		}

		switch ($position) {
			case 'aggressiv' :
				$this->unit->setBattleRow(Combat::AGGRESSIVE);
				break;
			case 'defensiv' :
				$this->unit->setBattleRow(Combat::DEFENSIVE);
				break;
			case 'fliehe' :
			case 'fliehen' :
			case 'flucht' :
				$this->unit->setBattleRow(Combat::REFUGEE);
				if ($this->unit->IsGuarding()) {
					$this->unit->setIsGuarding(false);
					$this->message(FightUnguardMessage::class);
				}
				break;
			case 'hinten' :
				$this->unit->setBattleRow(Combat::BACK);
				break;
			case 'nicht' :
				$this->unit->setBattleRow(Combat::BYSTANDER);
				if ($this->unit->IsGuarding()) {
					$this->unit->setIsGuarding(false);
					$this->message(FightUnguardMessage::class);
				}
				break;
			case 'vorn' :
			case 'vorne' :
				$this->unit->setBattleRow(Combat::FRONT);
				break;
			case 'vorsichtig' :
				$this->unit->setBattleRow(Combat::CAREFUL);
				break;
			default :
				throw new InvalidCommandException($this, 'Invalid position "' . $position . '".');
		}
		$this->message(FightMessage::class)->p($this->unit->BattleRow());
	}
}
