<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use Lemuria\Engine\Lemuria\Exception\ActionException;

/**
 * Actions change the world of Lemuria when a turn is running.
 */
interface Action
{
	public const BEFORE = -1;

	public const MIDDLE = 0;

	public const AFTER = 1;

	/**
	 * Get action as string.
	 *
	 * @return string
	 */
	public function __toString(): string;

	/**
	 * Get the priority;
	 *
	 * @return int
	 */
	public function Priority(): int;

	/**
	 * Check if the action has been prepared and is ready to execute.
	 *
	 * @return bool
	 */
	public function isPrepared(): bool;

	/**
	 * Prepare execution of the action.
	 *
	 * @return Action
	 */
	public function prepare(): Action;

	/**
	 * Execute the action.
	 *
	 * @return Action
	 * @throws ActionException
	 */
	public function execute(): Action;
}
