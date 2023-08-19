<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

/**
 * Immediates are commands that are executed immediately after creation.
 */
interface Immediate extends Command
{
	/**
	 * Skip the command.
	 */
	public function skip(): static;
}
