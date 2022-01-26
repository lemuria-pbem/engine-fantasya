<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\FightMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FightUnguardMessage;
use Lemuria\Model\Fantasya\Combat\BattleRow;

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
				$this->unit->setBattleRow(BattleRow::AGGRESSIVE);
				break;
			case 'defensiv' :
				$this->unit->setBattleRow(BattleRow::DEFENSIVE);
				break;
			case 'fliehe' :
			case 'fliehen' :
			case 'flucht' :
				$this->unit->setBattleRow(BattleRow::REFUGEE);
				if ($this->unit->IsGuarding()) {
					$this->unit->setIsGuarding(false);
					$this->message(FightUnguardMessage::class);
				}
				break;
			case 'hinten' :
				$this->unit->setBattleRow(BattleRow::BACK);
				break;
			case 'nicht' :
				$this->unit->setBattleRow(BattleRow::BYSTANDER);
				if ($this->unit->IsGuarding()) {
					$this->unit->setIsGuarding(false);
					$this->message(FightUnguardMessage::class);
				}
				break;
			case 'vorn' :
			case 'vorne' :
				$this->unit->setBattleRow(BattleRow::FRONT);
				break;
			case 'vorsichtig' :
				$this->unit->setBattleRow(BattleRow::CAREFUL);
				break;
			default :
				throw new InvalidCommandException($this, 'Invalid position "' . $position . '".');
		}
		$this->message(FightMessage::class)->p($this->unit->BattleRow());
	}

	#[Pure] protected function checkSize(): bool {
		return true;
	}
}
