<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use Lemuria\Model\Fantasya\Commodity\Potion\DrinkOfCreation as Potion;

final class DrinkOfCreation extends AbstractUnitApply
{
	public function apply(int $amount) {
		$this->getEffect()->setCount($amount)->setWeeks(Potion::WEEKS);
	}
}
