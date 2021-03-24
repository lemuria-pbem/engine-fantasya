<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Exception\ActionException;

/**
 * Actions change the world of Lemuria when a turn is running.
 */
interface Action extends \Stringable
{
	public const BEFORE = -1;

	public const MIDDLE = 0;

	public const AFTER = 1;

	/**
	 * Get action as string.
	 */
	#[Pure] public function __toString(): string;

	#[Pure] public function Priority(): int;

	/**
	 * Check if the action has been prepared and is ready to execute.
	 */
	public function isPrepared(): bool;

	/**
	 * Prepare execution of the action.
	 */
	public function prepare(): Action;

	/**
	 * Execute the action.
	 *
	 * @throws ActionException
	 */
	public function execute(): Action;
}
