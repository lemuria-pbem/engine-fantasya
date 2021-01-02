<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Command;
use Lemuria\Engine\Lemuria\Command\Handover\Give;
use Lemuria\Engine\Lemuria\Command\Handover\Grant;

/**
 * Implementation of command GIB.
 *
 * The command determines the sub command and delegates to it.
 *
 * - GIB <Unit> Kommando
 * - GIB <Unit> Alles
 * - GIB <Unit> <commodity>
 * - GIB <Unit> Alles <commodity>
 * - GIB <Unit> <amount> <commodity>
 */
final class Handover extends DelegatedCommand
{
	/**
	 * Create the delegate.
	 */
	protected function createDelegate(): Command {
		if (count($this->phrase) === 2) {
			$param = strtolower($this->phrase->getParameter(2));
			if ($param === 'kommando') {
				return new Grant($this->phrase, $this->context);
			}
		}
		return new Give($this->phrase, $this->context);
	}
}
