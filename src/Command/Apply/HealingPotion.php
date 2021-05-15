<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

final class HealingPotion extends AbstractUnitApply
{
	public function apply(int $amount) {
		$this->getEffect()->setCount($amount);
	}
}
