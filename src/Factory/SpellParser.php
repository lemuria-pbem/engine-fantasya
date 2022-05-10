<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use function Lemuria\isInt;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Exception\UnknownItemException;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\IdException;
use Lemuria\Exception\LemuriaException;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Spell;
use Lemuria\Model\Fantasya\Spell\AstralChaos;
use Lemuria\Model\Fantasya\Spell\AuraTransfer;
use Lemuria\Model\Fantasya\Spell\CivilCommotion;
use Lemuria\Model\Fantasya\Spell\Daydream;
use Lemuria\Model\Fantasya\Spell\EagleEye;
use Lemuria\Model\Fantasya\Spell\Earthquake;
use Lemuria\Model\Fantasya\Spell\Farsight;
use Lemuria\Model\Fantasya\Spell\Fireball;
use Lemuria\Model\Fantasya\Spell\GazeOfTheGriffin;
use Lemuria\Model\Fantasya\Spell\GustOfWind;
use Lemuria\Model\Fantasya\Spell\InciteMonster;
use Lemuria\Model\Fantasya\Spell\Quacksalver;
use Lemuria\Model\Fantasya\Spell\Quickening;
use Lemuria\Model\Fantasya\Spell\ShockWave;
use Lemuria\Model\Fantasya\Spell\SongOfPeace;
use Lemuria\Model\Fantasya\Spell\SoundlessShadow;
use Lemuria\Model\Fantasya\Spell\SummonEnts;

class SpellParser
{
	use BuilderTrait;

	/**
	 * Spell has no parameters.
	 */
	public final const NONE = 0;

	/**
	 * Spell has optional level.
	 */
	public final const LEVEL = 1;

	/**
	 * Spell has mandatory target unit ID.
	 */
	public final const TARGET = 2;

	/**
	 * Spell has optional target region ID.
	 */
	public final const REGION = 4;

	/**
	 * Spell has mandatory directions.
	 */
	public final const DIRECTIONS = 8;

	/**
	 * Spell has optional level and mandatory target unit ID.
	 */
	public final const LEVEL_AND_TARGET = self::LEVEL + self::TARGET;

	protected final const SYNTAX = [
		AstralChaos::class      => self::LEVEL,
		AuraTransfer::class     => self::LEVEL_AND_TARGET,
		CivilCommotion::class   => self::NONE,
		Daydream::class         => self::LEVEL_AND_TARGET,
		EagleEye::class         => self::LEVEL,
		Earthquake::class       => self::LEVEL,
		Farsight::class         => self::REGION,
		Fireball::class         => self::LEVEL,
		GazeOfTheGriffin::class => self::DIRECTIONS,
		GustOfWind::class       => self::NONE,
		InciteMonster::class    => self::TARGET,
		Quacksalver::class      => self::LEVEL,
		Quickening::class       => self::LEVEL,
		ShockWave::class        => self::LEVEL,
		SongOfPeace::class      => self::LEVEL,
		SoundlessShadow::class  => self::LEVEL,
		SummonEnts::class       => self::LEVEL
	];

	protected final const SPELLS = [
		'Astrales'       => ['Chaos' => AstralChaos::class],
		'Aufruhr'        => ['verursachen' => CivilCommotion::class],
		'Auratransfer'   => AuraTransfer::class,
		'Beschleunigung' => Quickening::class,
		'Blick'          => ['des' => ['Greifen' => GazeOfTheGriffin::class]],
		'Erdbeben'       => Earthquake::class,
		'Erwecke'        => ['Baumhirten' => SummonEnts::class],
		'Fernsicht'      => Farsight::class,
		'Feuerball'      => Fireball::class,
		'Friedenslied'   => SongOfPeace::class,
		'Lautloser'      => ['Schatten' => SoundlessShadow::class],
		'Monster'        => ['aufhetzen' => InciteMonster::class],
		'Schockwelle'    => ShockWave::class,
		'Sturmboe'       => GustOfWind::class,
		'Sturmböe'       => GustOfWind::class,
		'Tagtraum'       => Daydream::class,
		'Wunderdoktor'   => Quacksalver::class
	];

	protected readonly string $spell;

	protected readonly int $level;

	protected ?Id $target = null;

	protected ?DirectionList $directions = null;

	/**
	 * @throws UnknownItemException
	 */
	public static function getSyntax(Spell $spell): int {
		if (isset(self::SYNTAX[$spell::class])) {
			return self::SYNTAX[$spell::class];
		}
		throw new UnknownItemException($spell);
	}

	public function __construct(Phrase $phrase) {
		$spells = self::SPELLS;
		$i      = 1;
		$spell  = [];
		$config = null;
		do {
			$part = strtolower($phrase->getParameter($i++));
			foreach ($spells as $key => &$value) {
				if (strtolower($key) === $part) {
					if (is_string($value)) {
						$spell[] = $key;
						$config  = self::SYNTAX[$value];
					} else {
						$spells = $value;
					}
					break;
				}
			}
		} while ($config === null && $part);
		if (!$config) {
			throw new UnknownItemException($phrase);
		}
		$this->parseParameters($phrase, $i, $config, $spell);
	}

	public function Spell(): string {
		return $this->spell;
	}

	public function Level(): int {
		return $this->level;
	}

	public function Target(): ?Id {
		return $this->target;
	}

	public function Directions(): ?DirectionList {
		return $this->directions;
	}

	protected function parseParameters(Phrase $phrase, int $next, int $config, array $spell): void {
		$this->spell = implode(' ', $spell);
		switch ($config) {
			case self::LEVEL :
				$this->level = $this->parseOptionalLevel($phrase, $next);
				break;
			case self::TARGET :
				$this->parseTarget($phrase, $next);
				break;
			case self::REGION :
				$this->parseOptionalRegion($phrase, $next);
				break;
			case self::DIRECTIONS :
				$this->parseDirections($phrase, $next);
				break;
			case self::LEVEL_AND_TARGET :
				$this->parseOptionalLevelAndTarget($phrase, $next);
				break;
			default :
				throw new LemuriaException();
		}
	}

	protected function parseOptionalLevel(Phrase $phrase, int $next): int {
		$level = $phrase->getParameter($next);
		if (isInt($level)) {
			if ($phrase->count() > $next) {
				throw new UnknownCommandException($phrase);
			}
			return $this->parseLevel($level);
		}
		if ($level === '') {
			return 1;
		}
		throw new UnknownCommandException($phrase);
	}

	protected function parseTarget(Phrase $phrase, int $next): void {
		$target = $phrase->getParameter($next);
		try {
			$this->target = Id::fromId($target);
		} catch (IdException) {
			throw new InvalidCommandException($phrase);
		}
	}

	protected function parseDirections(Phrase $phrase, int $next): void {
		$context          = new Context(State::getInstance());
		$this->directions = new DirectionList($context);
		$n                = $phrase->count();
		while ($next <= $n) {
			$direction = $context->Factory()->direction($phrase->getParameter($next++));
			$this->directions->add($direction);
		}
	}

	protected function parseOptionalRegion(Phrase $phrase, int $next): void {
		$target = $phrase->getParameter($next);
		if (!empty($target)) {
			try {
				$this->target = Id::fromId($target);
			} catch (IdException) {
				throw new InvalidCommandException($phrase);
			}
		}
	}

	protected function parseOptionalLevelAndTarget(Phrase $phrase, int $next): void {
		$level  = $phrase->getParameter($next++);
		$target = $phrase->getParameter($next);
		if ($target) {
			if ($phrase->count() > $next) {
				throw new UnknownCommandException($phrase);
			}
			if (isInt($level)) {
				$this->level = $this->parseLevel($level);
			} elseif ($level !== '') {
				throw new UnknownCommandException($phrase);
			}
		} else {
			$this->level = 1;
		}
		try {
			$this->target = Id::fromId($target);
		} catch (IdException) {
			throw new InvalidCommandException($phrase);
		}
	}

	private function parseLevel(string $level): int {
		return max(1, (int)$level);
	}
}
