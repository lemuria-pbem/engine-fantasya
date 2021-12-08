<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use JetBrains\PhpStorm\Pure;

/**
 * Commands are executed by a Unit.
 */
interface Command extends Action
{
	/**
	 * Get the command ID.
	 */
	#[Pure] public function getId(): int;

	/**
	 * Get the delegate to execute.
	 */
	public function getDelegate(): Command;
}
