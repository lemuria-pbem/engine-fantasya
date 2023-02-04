<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

/**
 * Activities are commands that keep a unit busy the whole round, so it cannot do more than one activity per round.
 */
interface Activity
{
	/**
	 * Check if this activity is the unit's default activity.
	 */
	public function IsDefault(): bool;

	/**
	 * Set the default status.
	 */
	public function setIsDefault(bool $isDefault = true): void;

	/**
	 * Get the new default commands.
	 *
	 * @return array<Command>
	 */
	public function getNewDefaults(): array;

	/**
	 * Check if this activity allows execution of another activity.
	 */
	public function allows(Activity $activity): bool;
}
