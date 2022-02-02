<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Command\Use\Apply;
use Lemuria\Engine\Fantasya\Command\Use\Operate;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Exception\IdException;
use Lemuria\Id;

/**
 * Implementation of command BENUTZEN.
 *
 * The command determines the sub command and delegates to it.
 *
 * Apply:
 * - BENUTZEN <potion>
 * - BENUTZEN <amount> <potion>
 * Operate:
 * - BENUTZEN <Unicum>
 * - BENUTZEN <composition> <Unicum>
 */
final class UseCommand extends DelegatedCommand
{
	/**
	 * Create the delegate.
	 */
	protected function createDelegate(): Command {
		$n = count($this->phrase);
		if ($n < 1) {
			throw new InvalidCommandException($this);
		}

		$param = $this->phrase->getParameter();
		if ($n === 1) {
			try {
				$id = Id::fromId($param);
				if ($this->unit->Treasure()->has($id)) {
					return new Operate($this->phrase, $this->context);
				}
			} catch (IdException) {
			}
			return new Apply($this->phrase, $this->context);
		}

		$amount = (int)$param;
		if ((string)$amount === $param) {
			return new Apply($this->phrase, $this->context);
		}
		return new Operate($this->phrase, $this->context);
	}
}
