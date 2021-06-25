<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Exception\UnknownItemException;
use Lemuria\Engine\Fantasya\Phrase;

class SpellParser
{
	/**
	 * Spell has optional level.
	 */
	protected const LEvEL = 1;

	/**
	 * Spell has optional level and mandatory target unit ID.
	 */
	protected const LEVEL_AND_TARGET = 2;

	protected array $spells = [
		'Auratransfer' => self::LEVEL_AND_TARGET,
		'Feuerball'    => self::LEvEL,
		'Friedenslied' => self::LEvEL,
		'Schockwelle'  => self::LEvEL,
		'Wunderdoktor' => self::LEvEL
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

	public function __construct(Phrase $phrase) {
		$spells = &$this->spells;
		$i      = 1;
		$config = null;
		do {
			$part = strtolower($phrase->getParameter($i++));
			foreach ($spells as $key => &$value) {
				if (strtolower($key) === $part) {
					if (is_int($value)) {
						$config = $value;
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
		$this->parseParameters($phrase, $i, $config);
	}

	protected function parseParameters(Phrase $phrase, int $next, int $config): void {

	}
}
