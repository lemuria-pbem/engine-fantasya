<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria;

/**
 * Helper class for a single command.
 */
class Phrase implements \Countable
{
	/**
	 * @var array(string)
	 */
	protected array $parts = [];

	/**
	 * Initialize the command.
	 *
	 * @param string $command
	 */
	public function __construct(string $command) {
		foreach (explode(' ', $command) as $part) {
			$part = trim($part);
			if (strlen($part) > 0) {
				$this->parts[] = $part;
			}
		}
	}

	/**
	 * Get number of parameters.
	 *
	 * @return int
	 */
	public function count(): int {
		return count($this->parts) - 1;
	}

	/**
	 * Get the command phrase.
	 *
	 * @return string
	 */
	public function __toString(): string {
		return implode(' ', $this->parts);
	}

	/**
	 * Get the command verb.
	 *
	 * @return string
	 */
	public function getVerb(): string {
		return $this->parts[0] ?? '';
	}

	/**
	 * Get a command parameter.
	 *
	 * @param int $number
	 * @return string
	 */
	public function getParameter(int $number = 1): string {
		if ($number < 1) {
			$number = $this->count();
		}
		return $this->parts[$number] ?? '';
	}

	/**
	 * Get a string containing all parameters starting with given parameter, separated with space.
	 *
	 * @param int $from
	 * @return string
	 */
	public function getLine(int $from = 1): string {
		if ($from <= 0) {
			$from = 1;
		}
		if ($from > $this->count()) {
			return '';
		}
		$parts = array_slice($this->parts, $from);
		return implode(' ', $parts);
	}
}
