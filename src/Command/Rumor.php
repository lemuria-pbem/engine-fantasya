<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\RumorTrait;

/**
 * Add a rumor for visiting units.
 *
 * - GERÜCHT <text>
 */
final class Rumor extends UnitCommand
{
	use RumorTrait;

	protected function run(): void {
		$rumor = Describe::trimDescription($this->phrase->getLine());
		if ($rumor) {
			$this->createRumor($this->unit, $rumor);
		} else {
			throw new InvalidCommandException($this);
		}
	}
}
