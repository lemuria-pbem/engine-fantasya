<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use JetBrains\PhpStorm\Pure;

/**
 * Commands are executed by a Unit.
 */
interface Command extends Action
{
	/**
	 * Get the command ID.
	 *
	 * @return int
	 */
	#[Pure] public function getId(): int;

	/**
	 * Get the delegate to execute.
	 *
	 * @return Command
	 */
	public function getDelegate(): Command;
}
