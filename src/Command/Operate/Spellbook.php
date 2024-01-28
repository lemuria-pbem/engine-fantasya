<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Operate;

use Lemuria\Engine\Fantasya\Factory\LearnSpellTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\SpellbookNoSpellMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\SpellbookWriteAlreadyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\SpellbookWriteMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\SpellbookWriteUnknownMessage;
use Lemuria\Model\Fantasya\Composition\Spellbook as SpellbookModel;

final class Spellbook extends AbstractOperate
{
	private const DISINTEGRATE = 3 * 3;

	use BurnTrait;
	use LearnSpellTrait;

	public function apply(): void {
		$name      = $this->operator->Phrase()->getLine($this->operator->ArgumentIndex());
		$spell     = $this->context->Factory()->spell($name);
		$spellbook = $this->getSpellbook();
		$spells    = $spellbook->Spells();
		if (isset($spells[$spell])) {
			$this->learn($spell);
		} else {
			$unit   = $this->operator->Unit();
			$unicum = $this->operator->Unicum();
			$this->message(SpellbookNoSpellMessage::class, $unit)->s($spellbook)->e($unicum)->s($spell, SpellbookWriteMessage::SPELL);
		}
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
			$unicum->setComposition($spellbook);
			$this->message(SpellbookWriteMessage::class, $unit)->s($spellbook)->e($unicum)->s($spell, SpellbookWriteMessage::SPELL);
		} else {
			$this->message(SpellbookWriteUnknownMessage::class, $unit)->s($spellbook)->e($unicum)->s($spell, SpellbookWriteMessage::SPELL);
		}
	}

	protected function addLooseEffect(): void {
		$this->addDisintegrateEffectForRegion(self::DISINTEGRATE);
	}

	private function getSpellbook(): SpellbookModel {
		/** @var SpellbookModel $spellbook */
		$spellbook = $this->operator->Unicum()->Composition();
		return $spellbook;
	}
}
