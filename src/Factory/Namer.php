<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Model\Fantasya\Unit;

/**
 * Defines the interface of a name generator.
 */
interface Namer
{
	/**
	 * Name a Unit.
	 */
	public function name(Unit $unit): Unit;
}
