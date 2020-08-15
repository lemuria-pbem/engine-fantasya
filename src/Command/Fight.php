<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Exception\UnknownCommandException;
use Lemuria\Engine\Lemuria\Message\Unit\FightMessage;
use Lemuria\Engine\Lemuria\Message\Unit\FightUnguardMessage;
use Lemuria\Model\Lemuria\Combat;

/**
 * This command is used to set the unit's behaviour in combat.
 *
 * - KÄMPFEN [Agressiv|Defensiv|Fliehe|Fliehen|Flucht|Hinten|Nicht|Vorn|Vorne]
 */
final class Fight extends UnitCommand {

	/**
	 * The command implementation.
	 */
	protected function run(): void {
		$n = count($this->phrase);
		if ($n === 0) {
			$position = 'vorne';
		} elseif ($n === 1) {
			$position = strtolower($this->phrase->getParameter());
		} else {
			throw new UnknownCommandException($this);
		}

		switch ($position) {
			case 'agressiv' :
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
				break;
			case 'vorn' :
			case 'vorne' :
				$this->unit->setBattleRow(Combat::FRONT);
				break;
			default :
				throw new UnknownCommandException($this);
		}
		$this->message(FightMessage::class)->p($this->unit->BattleRow());
	}
}
