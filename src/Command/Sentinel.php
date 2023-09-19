<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Command\Sentinel\Block;
use Lemuria\Engine\Fantasya\Command\Sentinel\Guard;
use Lemuria\Engine\Fantasya\Command\Sentinel\Unguard;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;

/**
 * Implementation of command BEWACHEN.
 *
 * The command determines the sub command and delegates to it.
 *
 * - BEWACHEN
 * - BEWACHEN Region
 * - BEWACHEN <direction>
 * - BEWACHEN Nicht
 */
final class Sentinel extends DelegatedCommand
{
	protected function createDelegate(): Command {
		$n = count($this->phrase);
		if ($n === 0) {
			return new Guard($this->phrase, $this->context);
		}
		$param = strtolower($this->phrase->getParameter());
		if ($n === 1) {
			if ($param === 'region') {
				return new Guard($this->phrase, $this->context);
			}
			if ($param === 'nicht') {
				return new Unguard($this->phrase, $this->context);
			}
			return new Block($this->phrase, $this->context);
		}
		throw new InvalidCommandException($this);
	}
}
