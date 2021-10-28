<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

/**
 * Helper class for a single command.
 *
 * @noinspection PhpMultipleClassDeclarationsInspection
 */
#[Immutable]
class Phrase implements \Countable, \Stringable
{
	/**
	 * @var array(string)
	 */
	protected array $parts = [];

	#[Pure] public function __construct(string $command) {
		foreach (explode(' ', $command) as $part) {
			$part = trim($part);
			if (strlen($part) > 0) {
				$this->parts[] = $part;
			}
		}
	}

	/**
	 * Get number of parameters.
	 */
	#[Pure] public function count(): int {
		return count($this->parts) - 1;
	}

	/**
	 * Get the command phrase.
	 */
	#[Pure] public function __toString(): string {
		return implode(' ', $this->parts);
	}

	/**
	 * Get the command verb.
	 */
	#[Pure] public function getVerb(): string {
		$verb = $this->parts[0] ?? '';
		return mb_strtoupper($verb);
	}

	/**
	 * Get a command parameter.
	 */
	#[Pure] public function getParameter(int $number = 1): string {
		if ($number < 1) {
			$number = $this->count();
		}
		return $this->parts[$number] ?? '';
	}

	/**
	 * Get a string containing all parameters starting with given parameter, separated with space.
	 */
	#[Pure] public function getLine(int $from = 1): string {
		if ($from <= 0) {
			$from = 1;
		}
		if ($from > $this->count()) {
			return '';
		}
		$parts = array_slice($this->parts, $from);
		return implode(' ', $parts);
	}

	/**
	 * Get a string containing all parameters but the last one(s), separated by space.
	 */
	#[Pure] public function getLineUntil(int $offset = 1): string {
		if ($offset < 0) {
			$offset = -$offset;
		}
		$n = $this->count();
		if ($offset >= $n) {
			return '';
		}
		$parts = array_slice($this->parts, 0, $n - $offset);
		return implode(' ', $parts);
	}
}
