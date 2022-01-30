<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;

/**
 * This command creates a new Unicum.
 *
 * - ERSCHAFFEN <Composition>
 */
final class Unicum extends UnitCommand
{
	protected function run(): void {
		//TODO Unicum
		$n = $this->phrase->count();
		if ($n <= 0) {
			throw new InvalidCommandException($this, 'No composition given.');
		}
		$composition = $this->context->Factory()->composition($this->phrase->getParameter());
	}
}
