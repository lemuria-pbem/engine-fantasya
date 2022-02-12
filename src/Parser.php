<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Exception\CommandParserException;
use Lemuria\Engine\Move;
use Lemuria\Lemuria;

/**
 * Here the execution priority of all commands is determined.
 */
class Parser
{
	/**
	 * @var Phrase[]
	 */
	protected array $phrases = [];

	private int $index = 0;

	private readonly int $count;

	private int $skipLevel = 0;

	public function parse(Move $commands): Parser {
		foreach ($commands as $command) {
			$command = $this->replaceDefaultCommand(trim($command));
			$phrase  = new Phrase($command);
			if ($phrase->getVerb()) {
				$this->phrases[] = $phrase;
			}
		}
		$this->count = count($this->phrases);
		return $this;
	}

	/**
	 * Check if parser has more commands.
	 */
	#[Pure] public function hasMore(): bool {
		return $this->index < $this->count;
	}

	/**
	 * Check if current command shall be skipped.
	 */
	#[Pure] public function isSkip(): bool {
		return $this->skipLevel > 0;
	}

	/**
	 * Request end of command parsing.
	 */
	public function finish(): Parser {
		$this->index = $this->count;
		return $this;
	}

	/**
	 * Set pointer to next phrase.
	 *
	 * @throws CommandParserException
	 */
	public function next(): Phrase {
		if ($this->hasMore()) {
			return $this->phrases[$this->index++];
		}
		throw new CommandParserException('No more commands.');
	}

	/**
	 * Set or reset skipping mode.
	 */
	public function skip(bool $skip = true): Parser {
		if ($skip) {
			$this->skipLevel++;
			Lemuria::Log()->debug('Set skipping mode (level ' . $this->skipLevel . ').');
		} elseif ($this->skipLevel > 0) {
			$this->skipLevel--;
			Lemuria::Log()->debug('Reset skipping mode (level ' . $this->skipLevel . ').');
		} else {
			throw new CommandParserException('Not in skipping mode; cannot reset.');
		}
		return $this;
	}

	/**
	 * Default commands can have various forms:
	 *
	 * - @COMMAND is a short form of @ * COMMAND
	 * - +m COMMAND (m > 0) is a short form of @ m COMMAND
	 * - =n COMMAND (n > 0) is a short form of @ 0/n+1 COMMAND
	 * - +m =n COMMAND (m, n > 0) is a short form of @ m/n+1 COMMAND
	 * - =n +m COMMAND (m, n > 0) is a short form of @ m/n+1 COMMAND
	 */
	protected function replaceDefaultCommand(string $command): string {
		if (strlen($command) >= 2) {
			if ($command[0] === '@' && $command[1] !== ' ') {
				return '@ * ' . substr($command, 1);
			}

			if (preg_match('/^[+]([0-9]+) +[=]([0-9]+) +([^ ]+.*)$/', $command, $matches) === 1) {
				$m = (int)$matches[1];
				$n = (int)$matches[2];
				if ($n === 0) {
					$n = 1;
				}
				return '@ ' . $m . '/' . ++$n . ' ' . $matches[3];
			}

			if (preg_match('/^[=]([0-9]+) +[+]([0-9]+) +([^ ]+.*)$/', $command, $matches) === 1) {
				$m = (int)$matches[2];
				$n = (int)$matches[1];
				if ($n === 0) {
					$n = 1;
				}
				return '@ ' . $m . '/' . ++$n . ' ' . $matches[3];
			}

			if (preg_match('/^[+]([0-9]+) +([^ ]+.*)$/', $command, $matches) === 1) {
				$n = (int)$matches[1];
				return '@ ' . ($n >=2 ? $n . ' ' : '') . $matches[2];
			}

			if (preg_match('/^[=]([0-9]+) +([^ ]+.*)$/', $command, $matches) === 1) {
				$n = (int)$matches[1];
				if ($n === 0) {
					$n = 1;
				}
				return '@ 0/' . ++$n . ' ' . $matches[2];
			}
		}
		return $command;
	}
}
