<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Model\Fantasya\Unit;

interface Behaviour
{
	public function Unit(): Unit;

	public function setUnit(Unit $unit): Behaviour;

	public function conduct(): Behaviour;
}
