<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use function Lemuria\isInt;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\CastBattleSpellMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CastExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CastNoAuraMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CastNoMagicianMessage;
use Lemuria\Model\Fantasya\BattleSpell;
use Lemuria\Model\Fantasya\Spell;
use Lemuria\Model\Fantasya\Talent\Magic;

/**
 * Cast a spell.
 *
 * - ZAUBERN <spell>
 * - ZAUBERN <spell> <level>
 */
final class Cast extends UnitCommand
{
	private Spell $spell;

	private int $level;

	private int $knowledge;

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

	public function Knowledge(): int {
		return $this->knowledge;
	}

	protected function run(): void {
		$n = $this->phrase->count();
		if ($n < 1) {
			throw new UnknownCommandException($this);
		}

		$last = $this->phrase->getParameter(0);
		if (isInt($last)) {
			if ($n < 2) {
				throw new UnknownCommandException($this);
			}
			$level = (int)$last;
			$class = $this->phrase->getLineUntil();
		} else {
			$level = 1;
			$class = $this->phrase->getLine();
		}
		$this->spell = $this->context->Factory()->spell($class);
		if ($this->spell instanceof BattleSpell) {
			$this->message(CastBattleSpellMessage::class)->s($this->spell);
			return;
		}

		$this->level     = min($level, $this->getMaxLevel());
		$this->knowledge = $this->calculus()->knowledge(Magic::class)->Level();

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
		$cast->cast();
	}

	private function getMaxLevel(): int {
		if ($this->spell->IsIncremental()) {
			return (int)floor($this->unit->Aura()->Aura() / $this->spell->Aura());
		}
		return 1;
	}
}
