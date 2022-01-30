<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;

/**
 * The Name command is used to set the name of a unit or the construction, region or vessel it controls.
 *
 * - NAME Partei <Name>
 * - NAME [Einheit] <Name>
 * - NAME Burg|Gebäude <Name>
 * - NAME Region <Name>
 * - NAME Schiff <Name>
 * - NAME Kontinent|Insel <Name>
 */
final class Write extends UnitCommand
{
	protected function run(): void {
		//TODO Unicum
		$n = $this->phrase->count();
		if ($n <= 0) {
			throw new InvalidCommandException($this, 'No name given.');
		}
	}
}
