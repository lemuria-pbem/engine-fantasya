<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Command\Destroy\Deliver;
use Lemuria\Engine\Fantasya\Command\Destroy\Dismiss;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;

/**
 * Implementation of command ENTLASSEN.
 *
 * The command determines the sub command and delegates to it.
 *
 * Dismiss own unit:
 * - ENTLASSEN <unit>
 *
 * Dismiss summoned and controlled unit:
 * - ENTLASSEN <monster id>
 *
 * Delegates to Dismiss for commodities.
 */
final class Disband extends DelegatedCommand
{
	/**
	 * Create the delegate.
	 */
	protected function createDelegate(): Command {
		$n = count($this->phrase);
		if ($n < 1) {
			throw new InvalidCommandException($this, 'No recipient parameter in disband.');
		}

		if ($n > 1) {
			return new Dismiss($this->phrase, $this->context);
		}

		$unit = $this->nextId($n);
		if ($unit->Party() !== $this->context->Party()) {
			return new Deliver($this->phrase, $this->context);
		}
		throw new InvalidCommandException($this);
	}
}
