<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Command\Vacate\Abandon;
use Lemuria\Engine\Fantasya\Command\Vacate\Leave;

/**
 * This delegate command will create two leave commands with different priority (one before enter and one after) which
 * makes it possible to enter and leave a building at the same time if the resulting free space is sufficient.
 *
 * VERLASSEN
 */
final class Vacate extends DelegatedCommand
{
	protected function createDelegate(): Command {
		$command =  new CompositeCommand($this->phrase, $this->context);
		return $command->setCommands([
			new Abandon($this->phrase, $this->context),
			new Leave($this->phrase, $this->context)
		]);
	}
}
