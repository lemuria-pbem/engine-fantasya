<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Exception\ActionException;

/**
 * Actions change the world of Lemuria when a turn is running.
 */
interface Action extends \Stringable
{
	/**
	 * Get action as string.
	 */
	public function __toString(): string;

	public function Priority(): Priority;

	/**
	 * Check if the action has been prepared and is ready to execute.
	 */
	public function isPrepared(): bool;

	/**
	 * Prepare execution of the action.
	 */
	public function prepare(): static;

	/**
	 * Execute the action.
	 *
	 * @throws ActionException
	 */
	public function execute(): static;
}
