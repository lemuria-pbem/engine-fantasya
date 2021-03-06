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
 * Implementation of command GIB.
 *
 * The command determines the sub command and delegates to it.
 *
 * Give:
 * - GIB <Unit>
 * - GIB <Unit> Alles
 * - GIB <Unit> <commodity>
 * - GIB <Unit> Person|Personen
 * - GIB <Unit> Alles <commodity>
 * - GIB <Unit> <amount> <commodity>
 * - GIB <Unit> <amount> Person|Personen
 *
 * Grant:
 * - GIB <Unit> Kommando
 *
 * Migrate:
 * - GIB <Unit> Einheit
 *
 * Dismiss (Alias: ENTLASSEN)
 * - GIB Bauern
 * - GIB Bauern Alles|Einheit
 * - GIB Bauern <commodity>
 * - GIB Bauern Person|Personen
 * - GIB Bauern Alles <commodity>
 * - GIB Bauern <amount> <commodity>
 * - GIB Bauern <amount> Person|Personen
 *
 * Lose (Alias: VERLIEREN)
 * - GIB 0
 * - GIB 0 Alles
 * - GIB 0 <commodity>
 * - GIB 0 Person|Personen
 * - GIB 0 Alles <commodity>
 * - GIB 0 <amount> <commodity>
 * - GIB 0 <amount> Person|Personen
 */
final class Handover extends DelegatedCommand
{
	/**
	 * Create the delegate.
	 */
	protected function createDelegate(): Command {
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
