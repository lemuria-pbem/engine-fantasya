<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use JetBrains\PhpStorm\Pure;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Command\UnitCommand;

trait DefaultActivityTrait
{
	private bool $isDefault = false;

	/**
	 * Get the activity class.
	 */
	#[Pure] public function Activity(): string {
		return getClass($this);
	}

	/**
	 * Check if this activity is the unit's default activity.
	 */
	public function IsDefault(): bool {
		return $this->isDefault;
	}

	/**
	 * Get the new default command.
	 */
	public function getNewDefault(): ?UnitCommand {
		return $this;
	}

	/**
	 * Set the default status.
	 */
	public function setIsDefault(bool $isDefault = true): void {
		$this->isDefault = $isDefault;
	}
}
