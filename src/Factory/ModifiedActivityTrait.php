<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use JetBrains\PhpStorm\Pure;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Command\UnitCommand;

trait ModifiedActivityTrait
{
	use DefaultActivityTrait;

	private ?UnitCommand $newDefault = null;

	/**
	 * Get the activity class.
	 */
	#[Pure] public function Activity(): string {
		return getClass($this);
	}

	/**
	 * Get the new default command.
	 */
	public function getNewDefault(): ?UnitCommand {
		return $this->preventDefault ? null : $this->newDefault;
	}
}
