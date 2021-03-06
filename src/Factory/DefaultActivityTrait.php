<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Command\UnitCommand;

trait DefaultActivityTrait
{
	/**
	 * Get the new default command.
	 */
	public function getNewDefault(): ?UnitCommand {
		if ($this instanceof UnitCommand) {
			return $this;
		}
		return null;
	}
}
