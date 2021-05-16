<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

final class DrinkOfTruth extends AbstractUnitApply
{
	public function apply(int $amount): int {
		$this->getEffect()->setCount($amount);
		return $amount;
	}
}
