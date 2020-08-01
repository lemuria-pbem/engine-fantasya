<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use Lemuria\Engine\Lemuria\Exception\CommandParserException;
use Lemuria\Engine\Move;
use Lemuria\Lemuria;

/**
 * Here the execution priority of all commands is determined.
 */
class Parser
{
	protected Context $context;

	/**
	 * @var Phrase[]
	 */
	protected array $phrases = [];

	private int $index = 0;

	private int $count;

	private int $skipLevel = 0;

	/**
	 * @param Context $context
	 */
	public function __construct(Context $context) {
		$this->context = $context;
	}

	/**
	 * @param Move $commands
	 * @return Parser
	 */
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
	 *
	 * @return bool
	 */
	public function hasMore(): bool {
		return $this->index < $this->count;
	}

	/**
	 * Check if current command shall be skipped.
	 *
	 * @return bool
	 */
	public function isSkip(): bool {
		return $this->skipLevel > 0;
	}

	/**
	 * Request end of command parsing.
	 *
	 * @return Parser
	 */
	public function finish(): Parser {
		$this->index = $this->count;
		return $this;
	}

	/**
	 * Set pointer to next phrase.
	 *
	 * @return Phrase
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
	 *
	 * @param bool $skip
	 * @return Parser
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
