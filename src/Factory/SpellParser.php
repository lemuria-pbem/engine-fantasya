<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use function Lemuria\isInt;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Exception\IdException;
use Lemuria\Engine\Fantasya\Exception\UnknownItemException;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Exception\LemuriaException;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;

class SpellParser
{
	use BuilderTrait;

	/**
	 * Spell has optional level.
	 */
	protected const LEVEL = 1;

	/**
	 * Spell has optional level and mandatory target unit ID.
	 */
	protected const LEVEL_AND_TARGET = 2;

	protected const SPELLS = [
		'Auratransfer' => self::LEVEL_AND_TARGET,
		'Feuerball'    => self::LEVEL,
		'Friedenslied' => self::LEVEL,
		'Schockwelle'  => self::LEVEL,
		'Wunderdoktor' => self::LEVEL
		/*
		'Zauber' => [
			'mit' => [
				'mehreren' => [
					'Worten' => self::LEVEL
				]
			]
		]
		*/
	];

	protected string $spell;

	protected int $level;

	protected ?Id $target = null;

	public function __construct(Phrase $phrase) {
		$spells = self::SPELLS;
		$i      = 1;
		$spell  = [];
		$config = null;
		do {
			$part = strtolower($phrase->getParameter($i++));
			foreach ($spells as $key => &$value) {
				if (strtolower($key) === $part) {
					if (is_int($value)) {
						$spell[] = $key;
						$config  = $value;
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

	protected function parseParameters(Phrase $phrase, int $next, int $config, array $spell): void {
		$this->spell = implode(' ', $spell);
		switch ($config) {
			case self::LEVEL :
				$this->level = $this->parseOptionalLevel($phrase, $next);
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
