<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use Lemuria\Model\Fantasya\Commodity\Potion\Brainpower as Potion;

final class Brainpower extends AbstractUnitApply
{
	public function apply(int $amount): int {
		$this->getEffect()->setCount($amount)->setWeeks(Potion::WEEKS);
		return $amount;
	}
}
