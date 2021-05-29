<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Command\UnitCommand;

/**
 * Activities are commands that keep a unit busy the whole round, so it cannot do more than one activity per round.
 */
interface Activity
{
	/**
	 * Get the activity class.
	 */
	public function Activity(): string;

	/**
	 * Check if this activity is the unit's default activity.
	 */
	public function IsDefault(): bool;

	/**
	 * Get the new default command.
	 */
	public function getNewDefault(): ?UnitCommand;

	/**
	 * Set the default status.
	 */
	public function setIsDefault(bool $isDefault = true): void;
}
