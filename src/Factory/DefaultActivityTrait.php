<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Factory;

use Lemuria\Engine\Lemuria\Command\UnitCommand;

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
