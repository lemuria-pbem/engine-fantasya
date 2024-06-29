<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Exception\CommandParserException;
use Lemuria\Engine\Move;
use Lemuria\Lemuria;

/**
 * Here the execution priority of all commands is determined.
 */
class Parser
{
	/**
	 * @type array<string, array<string>>
	 */
	private const array DEPRECATION = [
		'VORLAGE * GEBEN' => ['LIEF', 'LIEFE', 'LIEFER', 'LIEFERE', 'LIEFERN']
	];

	/**
	 * @var array<Phrase>
	 */
	protected array $phrases = [];

	private int $index = 0;

	private readonly int $count;

	private int $skipLevel = 0;

	/**
	 * @var array<string, string>
	 */
	private array $replacement = [];

	public function __construct() {
		foreach (self::DEPRECATION as $replacement => $variants) {
			foreach ($variants as $variant) {
				$this->replacement[$variant] = $replacement;
			}
		}
	}

	public function parse(Move $commands): static {
		foreach ($commands as $command) {
			$command = $this->replaceDefaultCommand($command);
			$command = $this->replaceDeprecatedCommand($command);
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
	public function hasMore(): bool {
		return $this->index < $this->count;
	}

	/**
	 * Check if current command shall be skipped.
	 */
	public function isSkip(): bool {
		return $this->skipLevel > 0;
	}

	/**
	 * Request end of command parsing.
	 */
	public function finish(): static {
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
	public function skip(bool $skip = true): static {
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

	protected function replaceDeprecatedCommand(string $command): string {
		$i      = strpos($command, ' ');
		$search = mb_strtoupper($i > 0 ? substr($command, 0, $i) : $command);
		if (isset($this->replacement[$search])) {
			if ($i > 0) {
				return $this->replacement[$search] . substr($command, $i);
			}
			return $this->replacement[$search];
		}
		return $command;
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
