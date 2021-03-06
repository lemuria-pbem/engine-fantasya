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

	private int $count;

	private int $skipLevel = 0;

	public function __construct(protected Context $context) {
	}

	public function parse(Move $commands): Parser {
		foreach ($commands as $command) {
			$phrase = new Phrase($command);
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
}
