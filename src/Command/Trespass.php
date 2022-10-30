<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Command\Trespass\Board;
use Lemuria\Engine\Fantasya\Command\Trespass\Enter;
use Lemuria\Engine\Fantasya\Command\Vacate\Abandon;

/**
 * Implementation of command BETRETEN.
 *
 * The command determines the sub command and delegates to it.
 *
 * - BETRETEN <Construction>
 * - BETRETEN Burg|Gebäude|Gebaeude <Construction>
 * - BETRETEN Schiff <Vessel>
 */
final class Trespass extends DelegatedCommand
{
	/**
	 * Create the delegate.
	 */
	protected function createDelegate(): Command {
		$n = count($this->phrase);
		if ($n < 1) {
			throw new InvalidCommandException($this, 'No recipient parameter in trespass.');
		}
		if ($n === 1) {
			return new Enter($this->phrase, $this->context);
		}
		if ($n === 2) {
			$type = mb_strtolower($this->phrase->getParameter());
			try {
				$trespass = match ($type) {
					'burg', 'gebäude', 'gebaeude' => new Enter($this->phrase, $this->context),
					'schiff' => new Board($this->phrase, $this->context)
				};
				if ($this->unit->Construction()) {
					$command = new CompositeCommand($this->phrase, $this->context);
					return $command->setCommands([
						new Abandon($this->phrase, $this->context),
						$trespass
					]);
				}
				return $trespass;
			} catch (\UnhandledMatchError $e) {
				throw new UnknownCommandException($this, new CommandException('Invalid trespass type: ' . $type, previous: $e));
			}
		}
		throw new UnknownCommandException($this);
	}
}
