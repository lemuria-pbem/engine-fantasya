<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Command\Destroy\Dismiss;
use Lemuria\Engine\Fantasya\Command\Destroy\Lose;
use Lemuria\Engine\Fantasya\Command\Handover\Bestow;
use Lemuria\Engine\Fantasya\Command\Handover\Give;
use Lemuria\Engine\Fantasya\Command\Handover\Grant;
use Lemuria\Engine\Fantasya\Command\Handover\Migrate;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Exception\IdException;
use Lemuria\Id;

/**
 * Implementation of command GEBEN.
 *
 * The command determines the sub command and delegates to it.
 *
 * Give:
 * - GEBEN <Unit>
 * - GEBEN <Unit> Alles
 * - GEBEN <Unit> <commodity>
 * - GEBEN <Unit> Person|Personen
 * - GEBEN <Unit> Alles <commodity>
 * - GEBEN <Unit> <amount> <commodity>
 * - GEBEN <Unit> <amount> Person|Personen

 * Grant:
 * - GEBEN <Unit> Kommando
 *
 * Migrate:
 * - GEBEN <Unit> Einheit
 *
 * Dismiss (Alias: ENTLASSEN)
 * - GEBEN Bauern
 * - GEBEN Bauern Alles|Einheit
 * - GEBEN Bauern <commodity>
 * - GEBEN Bauern Person|Personen
 * - GEBEN Bauern Alles <commodity>
 * - GEBEN Bauern <amount> <commodity>
 * - GEBEN Bauern <amount> Person|Personen
 *
 * Lose (Alias: VERLIEREN)
 * - GEBEN 0
 * - GEBEN 0 Alles
 * - GEBEN 0 <commodity>
 * - GEBEN 0 Person|Personen
 * - GEBEN 0 Alles <commodity>
 * - GEBEN 0 <amount> <commodity>
 * - GEBEN 0 <amount> Person|Personen
 *
 * Bestow (Unicum)
 * - GEBEN <Unit> <Unicum>
 * - GEBEN <Unit> <composition> <Unicum>
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
			default  => null
		};
		if ($command) {
			return new $command($this->phrase, $this->context);
		}

		try {
			if ($n > 2) {
				if ($this->context->Factory()->isComposition($this->phrase->getParameter(2))) {
					$id = Id::fromId($this->phrase->getParameter(3));
					if ($this->unit->Treasury()->has($id)) {
						return new Bestow($this->phrase, $this->context);
					}
				}
			} elseif ($n === 2) {
				$id = Id::fromId($this->phrase->getParameter(2));
				if ($this->unit->Treasury()->has($id)) {
					return new Bestow($this->phrase, $this->context);
				}
			}
		} catch (IdException) {
		}

		return new Give($this->phrase, $this->context);
	}
}
