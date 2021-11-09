<?php
/** @noinspection GrazieInspection */
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use function Lemuria\isInt;
use Lemuria\Engine\Fantasya\Message\Unit\BattleSpellExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BattleSpellInvalidMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BattleSpellMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BattleSpellNoneSetMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BattleSpellNotSetMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BattleSpellUnknownMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BattleSpellNoMagicianMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BattleSpellRemoveAllMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BattleSpellRemoveMessage;
use Lemuria\Exception\SingletonException;
use Lemuria\Model\Fantasya\SpellGrade;
use Lemuria\Model\Fantasya\BattleSpell as BattleSpellInterface;
use Lemuria\Model\Fantasya\BattleSpells;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Model\Fantasya\Talent\Magic;

/**
 * Set spells for battle.
 *
 * - KAMPFZAUBER <spell>
 * - KAMPFZAUBER <spell> <level>
 * - KAMPFZAUBER <spell> Aus|Nicht
 * - KAMPFZAUBER Aus|Kein|Keine|Keiner|Keinen|Nicht
 */
final class BattleSpell extends UnitCommand
{
	protected function run(): void {
		$knowledge = $this->calculus()->knowledge(Magic::class)->Level();
		if ($knowledge <= 0) {
			$this->message(BattleSpellNoMagicianMessage::class);
			return;
		}

		$n = $this->phrase->count();
		if ($n < 1) {
			throw new UnknownCommandException($this);
		}

		$last = $this->phrase->getParameter(0);
		if ($n === 1 && in_array(strtolower($last), ['aus', 'kein', 'keine', 'keiner', 'keinen', 'nicht'])) {
			$this->removeAllBattleSpells();
			return;
		}

		if (isInt($last)) {
			if ($n < 2) {
				throw new UnknownCommandException($this);
			}
			$level = (int)$last;
			$class = $this->phrase->getLineUntil();
		} elseif (in_array(strtolower($last), ['aus', 'nicht'])) {
			$level = 0;
			$class = $this->phrase->getLineUntil();
		} else {
			$level = 1;
			$class = $this->phrase->getLine();
		}

		try {
			$spell = $this->context->Factory()->spell($class);
		} catch (SingletonException) {
			$this->message(BattleSpellUnknownMessage::class)->p($class);
			return;
		}
		$spellBook = $this->unit->Party()->SpellBook();
		if (!isset($spellBook[$spell])) {
			$this->message(BattleSpellUnknownMessage::class)->p($class);
			return;
		}
		if (!($spell instanceof BattleSpellInterface)) {
			$this->message(BattleSpellInvalidMessage::class)->s($spell);
			return;
		}
		if ($knowledge < $spell->Difficulty()) {
			$this->message(BattleSpellExperienceMessage::class)->s($spell);
			return;
		}

		if ($level <= 0) {
			$this->removeBattleSpell($spell);
		} else {
			if (!$spell->IsIncremental()) {
				$level = 1;
			}
			$this->addBattleSpell(new SpellGrade($spell, $level));
		}
	}

	private function addBattleSpell(SpellGrade $spell): void {
		$battleSpells = $this->unit->BattleSpells();
		if (!$battleSpells) {
			$battleSpells = new BattleSpells();
		}
		$battleSpells->add($spell);
		$this->unit->setBattleSpells($battleSpells);
		$this->message(BattleSpellMessage::class)->s($spell->Spell());
	}

	private function removeBattleSpell(BattleSpellInterface $spell): void {
		$battleSpells = $this->unit->BattleSpells();
		if ($battleSpells && $battleSpells->has($spell)) {
			$battleSpells->remove($spell);
			$this->message(BattleSpellRemoveMessage::class)->s($spell);
			if (count($battleSpells) <= 0) {
				$this->unit->setBattleSpells(null);
			}
		} else {
			$this->message(BattleSpellNotSetMessage::class)->s($spell);
		}
	}

	private function removeAllBattleSpells(): void {
		$battleSpells = $this->unit->BattleSpells();
		if ($battleSpells && count($battleSpells) > 0) {
			$this->unit->setBattleSpells(null);
			$this->message(BattleSpellRemoveAllMessage::class);
		} else {
			$this->message(BattleSpellNoneSetMessage::class);
		}
	}
}
