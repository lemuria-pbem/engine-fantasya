<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

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
	 * Get the new default commands.
	 *
	 * @return Command[]
	 */
	public function getNewDefaults(): array;

	/**
	 * Set the default status.
	 */
	public function setIsDefault(bool $isDefault = true): void;

	/**
	 * Prevent that this activity is used as new default.
	 */
	public function preventDefault(): Activity;

	/**
	 * Check if this activity allows execution of another activity.
	 */
	public function allows(Activity $activity): bool;
}
