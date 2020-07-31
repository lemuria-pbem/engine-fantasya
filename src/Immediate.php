<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria;

/**
 * Immediates are commands that are executed immediately after creation.
 */
interface Immediate extends Command
{
	/**
	 * Skip the command.
	 *
	 * @return Immediate
	 */
	public function skip(): Immediate;
}
