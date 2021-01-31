<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Command;
use Lemuria\Engine\Lemuria\Command\Destroy\Dismiss;
use Lemuria\Engine\Lemuria\Command\Destroy\Smash;

/**
 * Implementation of command ZERSTÖREN.
 *
 * The command determines the sub command and delegates to it.
 *
 * Dismiss:
 * - ZERSTÖREN
 * - ZERSTÖREN Alles|Einheit
 * - ZERSTÖREN <commodity>
 * - ZERSTÖREN Person/Personen
 * - ZERSTÖREN Alles <commodity>
 * - ZERSTÖREN <amount> <commodity>
 * - ZERSTÖREN <amount> Person|Personen
 *
 * Smash (activity):
 * - ZERSTÖREN Burg|Gebäude|Gebaeude
 * - ZERSTÖREN Schiff
 */
final class Destroy extends DelegatedCommand
{
	/**
	 * Create the delegate.
	 */
	protected function createDelegate(): Command {
		if (count($this->phrase) === 2) {
			$param = strtolower($this->phrase->getParameter(2));
			switch ($param) {
				case 'burg' :
				case 'gebäude' :
				case 'gebaeude' :
				case 'schiff' :
					return new Smash($this->phrase, $this->context);
			}
		}
		return new Dismiss($this->phrase, $this->context);
	}
}
