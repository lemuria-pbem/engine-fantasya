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
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Spell;
use Lemuria\Model\Fantasya\Spell\AstralChaos;
use Lemuria\Model\Fantasya\Spell\AstralPassage;
use Lemuria\Model\Fantasya\Spell\AuraTransfer;
use Lemuria\Model\Fantasya\Spell\CivilCommotion;
use Lemuria\Model\Fantasya\Spell\Daydream;
use Lemuria\Model\Fantasya\Spell\DetectMetals;
use Lemuria\Model\Fantasya\Spell\EagleEye;
use Lemuria\Model\Fantasya\Spell\Earthquake;
use Lemuria\Model\Fantasya\Spell\ElementalBeing;
use Lemuria\Model\Fantasya\Spell\Farsight;
use Lemuria\Model\Fantasya\Spell\Fireball;
use Lemuria\Model\Fantasya\Spell\GazeOfTheBasilisk;
use Lemuria\Model\Fantasya\Spell\GazeOfTheGriffin;
use Lemuria\Model\Fantasya\Spell\GhostEnemy;
use Lemuria\Model\Fantasya\Spell\GustOfWind;
use Lemuria\Model\Fantasya\Spell\InciteMonster;
use Lemuria\Model\Fantasya\Spell\Quacksalver;
use Lemuria\Model\Fantasya\Spell\Quickening;
use Lemuria\Model\Fantasya\Spell\RaiseTheDead;
use Lemuria\Model\Fantasya\Spell\RestInPeace;
use Lemuria\Model\Fantasya\Spell\RingOfInvisibility;
use Lemuria\Model\Fantasya\Spell\RustyMist;
use Lemuria\Model\Fantasya\Spell\ShockWave;
use Lemuria\Model\Fantasya\Spell\SongOfPeace;
use Lemuria\Model\Fantasya\Spell\SoundlessShadow;
use Lemuria\Model\Fantasya\Spell\StoneSkin;
use Lemuria\Model\Fantasya\Spell\SummonEnts;
use Lemuria\Model\Fantasya\Spell\Teleportation;

class SpellParser
{
	use BuilderTrait;

	/**
	 * Spell has no parameters.
	 */
	public final const int NONE = 0;

	/**
	 * Spell has optional level.
	 */
	public final const int LEVEL = 1;

	/**
	 * Spell has optional target domain.
	 */
	public final const int DOMAIN = 2;

	/**
	 * Spell has mandatory target unit ID.
	 */
	public final const int TARGET = 4;

	/**
	 * Spell has optional target region ID.
	 */
	public final const int REGION = 8;

	/**
	 * Spell has mandatory directions.
	 */
	public final const int DIRECTIONS = 16;

	/**
	 * Spell has optional level and mandatory target unit ID.
	 */
	public final const int LEVEL_AND_TARGET = self::LEVEL + self::TARGET;

	/**
	 * Spell has optional target domain and mandatory target ID.
	 */
	public final const int DOMAIN_AND_TARGET = self::DOMAIN + self::TARGET;

	/**
	 * Spell has mandatory target ID and optional level.
	 *
	 * If level is not given, it is set to the maximum.
	 */
	public final const int TARGET_AND_LEVEL = self::TARGET - self::LEVEL;

	/**
	 * @type array<string, int>
	 */
	protected final const array SYNTAX = [
		AstralChaos::class        => self::LEVEL,
		AstralPassage::class      => self::DOMAIN_AND_TARGET,
		AuraTransfer::class       => self::LEVEL_AND_TARGET,
		CivilCommotion::class     => self::NONE,
		Daydream::class           => self::LEVEL_AND_TARGET,
		DetectMetals::class       => self::NONE,
		EagleEye::class           => self::LEVEL,
		Earthquake::class         => self::LEVEL,
		ElementalBeing::class     => self::NONE,
		Farsight::class           => self::REGION,
		Fireball::class           => self::LEVEL,
		GazeOfTheBasilisk::class  => self::LEVEL,
		GazeOfTheGriffin::class   => self::DIRECTIONS,
		GhostEnemy::class         => self::LEVEL,
		GustOfWind::class         => self::NONE,
		InciteMonster::class      => self::TARGET,
		Quacksalver::class        => self::LEVEL,
		Quickening::class         => self::LEVEL,
		RaiseTheDead::class       => self::LEVEL,
		RestInPeace::class        => self::NONE,
		RingOfInvisibility::class => self::NONE,
		RustyMist::class          => self::LEVEL,
		ShockWave::class          => self::LEVEL,
		SongOfPeace::class        => self::LEVEL,
		SoundlessShadow::class    => self::LEVEL,
		StoneSkin::class          => self::LEVEL,
		SummonEnts::class         => self::LEVEL,
		Teleportation::class      => self::TARGET_AND_LEVEL
	];

	/**
	 * @type array<string, mixed>
	 */
	protected final const array SPELLS = [
		'Astrales'        => ['Chaos'       => AstralChaos::class],
		'Astraler'        => ['Weg'         => AstralPassage::class],
		'Aufruhr'         => ['verursachen' => CivilCommotion::class],
		'Auratransfer'    => AuraTransfer::class,
		'Beschleunigung'  => Quickening::class,
		'Blick'           => ['des'         => ['Basilisken' => GazeOfTheBasilisk::class, 'Greifen' => GazeOfTheGriffin::class]],
		'Elementarwesen'  => ElementalBeing::class,
		'Erdbeben'        => Earthquake::class,
		'Erwecke'         => ['Baumhirten'  => SummonEnts::class],
		'Fernsicht'       => Farsight::class,
		'Feuerball'       => Fireball::class,
		'Friedenslied'    => SongOfPeace::class,
		'Geisterkaempfer' => GhostEnemy::class,
		'Geisterkämpfer'  => GhostEnemy::class,
		'Lautloser'       => ['Schatten'    => SoundlessShadow::class],
		'Metalle'         => ['entdecken'   => DetectMetals::class],
		'Monster'         => ['aufhetzen'   => InciteMonster::class],
		'Ring'            => ['der'         => ['Unsichtbarkeit' => RingOfInvisibility::class]],
		'Rosthauch'       => RustyMist::class,
		'Ruhe'            => ['in'          => ['Frieden'        => RestInPeace::class]],
		'Schockwelle'     => ShockWave::class,
		'Steinhaut'       => StoneSkin::class,
		'Sturmboe'        => GustOfWind::class,
		'Sturmböe'        => GustOfWind::class,
		'Tagtraum'        => Daydream::class,
		'Teleportation'   => Teleportation::class,
		'Untote'          => ['erwecken'    => RaiseTheDead::class],
		'Wunderdoktor'    => Quacksalver::class
	];

	protected readonly string $spell;

	protected readonly int $level;

	protected ?Domain $domain = null;

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

	public function __construct(protected Context $context, Phrase $phrase) {
		$spells = self::SPELLS;
		$i      = 1;
		$spell  = [];
		$config = null;
		do {
			$part = mb_strtolower($phrase->getParameter($i++));
			foreach ($spells as $key => &$value) {
				if (mb_strtolower($key) === $part) {
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

	public function Domain(): ?Domain {
		return $this->domain;
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
			case self::TARGET_AND_LEVEL :
				$this->parseOptionalLevelAndTarget($phrase, $next, PHP_INT_MAX);
				break;
			case self::DOMAIN_AND_TARGET :
				$this->parseOptionalDomainAndTarget($phrase, $next);
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

	protected function parseOptionalLevelAndTarget(Phrase $phrase, int $next, int $default = 1): void {
		$level  = $phrase->getParameter($next++);
		$target = $phrase->getParameter($next);
		if ($target) {
			if ($phrase->count() > $next) {
				throw new UnknownCommandException($phrase);
			}
			if (isInt($level)) {
				$this->level = $this->parseLevel($level);
			} else {
				if ($level !== '') {
					throw new UnknownCommandException($phrase);
				}
				$this->level = $default;
			}
		} else {
			$this->level = $default;
		}
		try {
			$this->target = Id::fromId($target);
		} catch (IdException) {
			throw new InvalidCommandException($phrase);
		}
	}

	protected function parseOptionalDomainAndTarget(Phrase $phrase, int $next): void {
		$domain = $phrase->getParameter($next++);
		$target = $phrase->getParameter($next);
		if ($target) {
			if ($phrase->count() > $next) {
				throw new UnknownCommandException($phrase);
			}
			$this->domain = $this->context->Factory()->domain($domain);
		} else {
			$this->domain = null;
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
