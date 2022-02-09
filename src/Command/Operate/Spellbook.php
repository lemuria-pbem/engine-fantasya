<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Operate;

use Lemuria\Engine\Fantasya\Factory\LearnSpellTrait;
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
			//TODO already in bool
			return;
		}
		$knownSpells = $unit->Party()->SpellBook();
		if (isset($knownSpells[$spell])) {
			$spells->add($spell);
			//TODO written
		} else {
			//TODO unknown
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
