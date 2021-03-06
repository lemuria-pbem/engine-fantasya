<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Command\Destroy\Dismiss;
use Lemuria\Engine\Fantasya\Command\Destroy\Smash;

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
 * - ZERSTÖREN Burg|Gebäude|Gebaeude <construction>
 * - ZERSTÖREN Schiff <vessel>
 */
final class Destroy extends DelegatedCommand
{
	/**
	 * Create the delegate.
	 */
	protected function createDelegate(): Command {
		if (count($this->phrase) === 2) {
			$param = strtolower($this->phrase->getParameter());
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
