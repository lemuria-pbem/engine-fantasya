<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Command;
use Lemuria\Exception\LemuriaException;

/**
 * Base class for composite commands that delegate to a number of commands.
 */
class CompositeCommand extends UnitCommand
{
	protected array $commands;

	/**
	 * @return Command[]
	 */
	public function getCommands(): array {
		return $this->commands;
	}

	/**
	 * @param Command[] $commands
	 */
	public function setCommands(array $commands): Command {
		foreach ($commands as $command) {
			if (!($command instanceof Command)) {
				throw new LemuriaException();
			}
		}
		$this->commands = $commands;
		return $this;
	}
}
