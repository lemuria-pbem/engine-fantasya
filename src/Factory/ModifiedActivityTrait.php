<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Command\UnitCommand;

trait ModifiedActivityTrait
{
	use DefaultActivityTrait;

	private ?UnitCommand $newDefault = null;

	/**
	 * Get the new default command.
	 */
	public function getNewDefault(): ?UnitCommand {
		return $this->preventDefault ? null : $this->newDefault;
	}
}
