<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Factory\SpellParser;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Message\Unit\CastBattleSpellMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CastExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CastMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CastNoAuraMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CastNoMagicianMessage;
use Lemuria\Model\Fantasya\BattleSpell;
use Lemuria\Model\Fantasya\Spell;
use Lemuria\Model\Fantasya\Talent\Magic;
use Lemuria\Model\Fantasya\Unit;

/**
 * Cast a spell.
 *
 * - ZAUBERN <spell>
 * - ZAUBERN <spell> <level>
 * - ZAUBERN <spell> <additional parameters>
 * - ZAUBERN <spell> <level> <additional parameters>
 */
final class Cast extends UnitCommand
{
	private Spell $spell;

	private int $level;

	private int $knowledge;

	private ?Unit $target;

	public function Context(): Context {
		return $this->context;
	}

	public function Aura(): int {
		return $this->spell->Aura() * $this->level;
	}

	public function Spell(): Spell {
		return $this->spell;
	}

	public function Level(): int {
		return $this->level;
	}

	public function Target(): ?Unit {
		return $this->target;
	}

	public function Knowledge(): int {
		return $this->knowledge;
	}

	protected function run(): void {
		$parser = new SpellParser($this->phrase);
		$spell  = $this->context->Factory()->spell($parser->Spell());
		if ($spell instanceof BattleSpell) {
			$this->message(CastBattleSpellMessage::class)->s($spell);
			return;
		}

		$this->spell     = $spell;
		$this->level     = min($parser->Level(), $this->getMaxLevel());
		$this->knowledge = $this->calculus()->knowledge(Magic::class)->Level();
		$this->target    = Unit::get($parser->Target());

		if ($this->knowledge <= 0) {
			$this->message(CastNoMagicianMessage::class);
			return;
		}
		if ($this->knowledge < $this->spell->Difficulty()) {
			$this->message(CastExperienceMessage::class)->s($this->spell);
			return;
		}
		if ($this->level <= 0) {
			$this->message(CastNoAuraMessage::class)->s($this->spell);
			return;
		}

		$cast = $this->context->Factory()->castSpell($this->spell, $this);
		$this->message(CastMessage::class)->s($this->spell);
		$cast->cast();
	}

	private function getMaxLevel(): int {
		if ($this->spell->IsIncremental()) {
			return (int)floor($this->unit->Aura()->Aura() / $this->spell->Aura());
		}
		return 1;
	}
}
