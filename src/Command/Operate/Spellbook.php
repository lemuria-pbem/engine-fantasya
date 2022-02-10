<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Operate;

use Lemuria\Engine\Fantasya\Factory\LearnSpellTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\SpellbookWriteAlreadyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\SpellbookWriteMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\SpellbookWriteUnknownMessage;
use Lemuria\Model\Fantasya\Composition\Spellbook as SpellbookModel;

final class Spellbook extends AbstractOperate
{
	use LearnSpellTrait;

	public function apply(): void {
		$name  = $this->operator->Phrase()->getLine($this->operator->ArgumentIndex());
		$spell = $this->context->Factory()->spell($name);
		$this->learn($spell);
	}

	public function write(): void {
		$unit      = $this->operator->Unit();
		$unicum    = $this->operator->Unicum();
		$spellbook = $this->getSpellbook();
		$spells    = $spellbook->Spells();

		$name  = $this->operator->Phrase()->getLine($this->operator->ArgumentIndex());
		$spell = $this->context->Factory()->spell($name);
		if (isset($spells[$spell])) {
			$this->message(SpellbookWriteAlreadyMessage::class, $unit)->s($spellbook)->e($unicum)->s($spell, SpellbookWriteMessage::SPELL);
			return;
		}
		$knownSpells = $unit->Party()->SpellBook();
		if (isset($knownSpells[$spell])) {
			$spells->add($spell);
			$this->message(SpellbookWriteMessage::class, $unit)->s($spellbook)->e($unicum)->s($spell, SpellbookWriteMessage::SPELL);
		} else {
			$this->message(SpellbookWriteUnknownMessage::class, $unit)->s($spellbook)->e($unicum)->s($spell, SpellbookWriteMessage::SPELL);
		}
	}


	/**
	 * @noinspection PhpUnnecessaryLocalVariableInspection
	 */
	private function getSpellbook(): SpellbookModel {
		/** @var SpellbookModel $spellbook */
		$spellbook = $this->operator->Unicum()->Composition();
		return $spellbook;
	}
}
