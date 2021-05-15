<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

final class WaterOfLife extends AbstractUnitApply
{
	public function apply(int $amount) {
		$this->getEffect()->setCount($amount);
		//TODO: Create saplings.
	}
}
