<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Command\Destroy\Dismiss;
use Lemuria\Engine\Fantasya\Command\Destroy\Lose;
use Lemuria\Engine\Fantasya\Command\Handover\Give;
use Lemuria\Engine\Fantasya\Command\Handover\Grant;
use Lemuria\Engine\Fantasya\Command\Handover\Migrate;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;

/**
 * Implementation of command BENUTZEN.
 *
 * The command determines the sub command and delegates to it.
 *
 * Apply:
 * - GIB <Unit>
 */
final class UseCommand extends DelegatedCommand
{
	/**
	 * Create the delegate.
	 */
	protected function createDelegate(): Command {
		//TODO Unicum
		$n = count($this->phrase);
		if ($n < 1) {
			throw new InvalidCommandException($this, 'No recipient parameter in handover.');
		}

		if ($n === 2) {
			$param = strtolower($this->phrase->getParameter(2));
			switch ($param) {
				case 'kommando' :
					return new Grant($this->phrase, $this->context);
				case 'einheit' :
					return new Migrate($this->phrase, $this->context);
			}
		}

		$command = match (strtolower($this->phrase->getParameter())) {
			'bauern' => Dismiss::class,
			'0'      => Lose::class,
			default  => Give::class
		};
		return new $command($this->phrase, $this->context);
	}
}
