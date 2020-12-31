<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Command;
use Lemuria\Engine\Lemuria\Command\Sentinel\Guard;
use Lemuria\Engine\Lemuria\Command\Sentinel\Unguard;
use Lemuria\Engine\Lemuria\Exception\UnknownCommandException;

/**
 * Implementation of command BEWACHEN.
 *
 * The command determines the sub command and delegates to it.
 *
 * - BEWACHEN
 * - BEWACHEN Nicht
 */
final class Sentinel extends DelegatedCommand
{
	protected function createDelegate(): Command {
		$n = count($this->phrase);
		if ($n === 0) {
			return new Guard($this->phrase, $this->context);
		}
		if ($n === 1) {
			$param = strtolower($this->phrase->getParameter());
			if ($param === 'nicht') {
				return new Unguard($this->phrase, $this->context);
			}
		}
		throw new UnknownCommandException($this);
	}
}
