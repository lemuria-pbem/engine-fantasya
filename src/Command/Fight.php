<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
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
		$position = $this->phrase->getParameter();
		try {
			$battleRow = $this->context->Factory()->battleRow($position);
		} catch (UnknownCommandException) {
			throw new InvalidCommandException($this, 'Invalid position ' . $position . '.');
		}

		$this->unit->setBattleRow($battleRow);
		if (in_array($battleRow, [BattleRow::BYSTANDER, BattleRow::REFUGEE])) {
			if ($this->unit->IsGuarding()) {
				$this->unit->setIsGuarding(false);
				$this->message(FightUnguardMessage::class);
			}
		}
		$this->message(FightMessage::class)->p($this->unit->BattleRow()->value);
	}

	#[Pure] protected function checkSize(): bool {
		return true;
	}
}
