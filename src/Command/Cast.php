<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\ActionException;
use Lemuria\Engine\Fantasya\Factory\DirectionList;
use Lemuria\Engine\Fantasya\Factory\SpellParser;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Message\Unit\CastBattleSpellMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CastExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CastMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CastNoAuraMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CastNoMagicianMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CastOnlyMessage;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\BattleSpell;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Spell;
use Lemuria\Model\Fantasya\Talent\Magic;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Fantasya\Vessel;

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

	private ?Region $region;

	private ?Construction $construction;

	private ?Vessel $vessel;

	private ?DirectionList $directions;

	private ?ActionException $exception = null;

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

	public function Region(): ?Region {
		return $this->region;
	}

	public function Construction(): ?Construction {
		return $this->construction;
	}

	public function Vessel(): ?Vessel {
		return $this->vessel;
	}

	public function Directions(): ?DirectionList {
		return $this->directions;
	}

	public function Knowledge(): int {
		return $this->knowledge;
	}

	public function cast(): void {
		$demandLevel     = $this->level;
		$this->level     = min($this->level, $this->getMaxLevel());
		$this->knowledge = $this->calculus()->knowledge(Magic::class)->Level();

		if ($this->spell instanceof BattleSpell) {
			$this->message(CastBattleSpellMessage::class)->s($this->spell);
			return;
		}
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
		if ($demandLevel > $this->level) {
			$this->message(CastOnlyMessage::class)->s($this->spell)->p($this->level);
		} else {
			$this->message(CastMessage::class)->s($this->spell);
		}
		$cast->cast();
	}

	public function setException(ActionException $exception): void {
		$this->exception = $exception;
	}

	protected function initialize(): void {
		parent::initialize();
		$parser             = new SpellParser($this->context, $this->phrase);
		$domain             = $parser->Domain();
		$target             = $parser->Target();
		$this->spell        = $this->context->Factory()->spell($parser->Spell());
		$this->level        = $parser->Level();
		$syntax             = SpellParser::getSyntax($this->spell);
		$this->target       = ($syntax | SpellParser::TARGET) && $target ? Unit::get($target) : null;
		$this->region       = ($syntax | SpellParser::REGION) && $target ? Region::get($target) : null;
		$this->region       = ($syntax | SpellParser::DOMAIN) && in_array($domain, [null, Domain::LOCATION]) && $target ? Region::get($target) : null;
		$this->construction = ($syntax | SpellParser::DOMAIN) && $domain === Domain::CONSTRUCTION && $target ? Construction::get($target) : null;
		$this->vessel       = ($syntax | SpellParser::DOMAIN) && $domain === Domain::VESSEL && $target ? Vessel::get($target) : null;
		$this->directions   = $parser->Directions();
		$this->context->getCasts()->add($this);
	}

	protected function run(): void {
		$this->context->getCasts()->cast();
		if ($this->exception) {
			throw $this->exception;
		}
	}

	private function getMaxLevel(): int {
		if ($this->spell->IsIncremental()) {
			return (int)floor($this->unit->Aura()->Aura() / $this->spell->Aura());
		}
		return 1;
	}
}
