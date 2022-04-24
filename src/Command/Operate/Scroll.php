<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Operate;

use Lemuria\Engine\Fantasya\Factory\LearnSpellTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\ScrollEmptyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\ScrollWriteMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\ScrollWriteNothingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\ScrollWriteUnknownMessage;
use Lemuria\Model\Fantasya\Composition\Scroll as ScrollModel;

final class Scroll extends AbstractOperate
{
	private const DISINTEGRATE = 3;

	use BurnTrait;
	use LearnSpellTrait;

	public function apply(): void {
		$spell = $this->getScroll()->Spell();
		if ($spell) {
			$this->learn($spell);
		} else {
			$unit        = $this->operator->Unit();
			$unicum      = $this->operator->Unicum();
			$composition = $unicum->Composition();
			$this->message(ScrollEmptyMessage::class, $unit)->s($composition)->e($unicum);
		}
	}

	public function write(): void {
		$unit   = $this->operator->Unit();
		$unicum = $this->operator->Unicum();
		$scroll = $this->getScroll();
		if ($scroll->Spell()) {
			$this->message(ScrollWriteNothingMessage::class, $unit)->e($unicum)->s($scroll);
			return;
		}

		$name        = $this->operator->Phrase()->getLine($this->operator->ArgumentIndex());
		$spell       = $this->context->Factory()->spell($name);
		$knownSpells = $unit->Party()->SpellBook();
		if (isset($knownSpells[$spell])) {
			$scroll->setSpell($spell);
			$this->message(ScrollWriteMessage::class, $unit)->e($unicum)->s($scroll)->s($spell, ScrollWriteMessage::SPELL);
		} else {
			$this->message(ScrollWriteUnknownMessage::class, $unit)->e($unicum)->s($scroll)->s($spell, ScrollWriteMessage::SPELL);
		}
	}

	protected function addLooseEffect(): void {
		$this->addDisintegrateEffectForRegion(self::DISINTEGRATE);
	}

	private function getScroll(): ScrollModel {
		/** @var ScrollModel $scroll */
		$scroll = $this->operator->Unicum()->Composition();
		return $scroll;
	}
}
