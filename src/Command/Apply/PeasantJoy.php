<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use Lemuria\Engine\Fantasya\Factory\ActionTrait;
use Lemuria\Engine\Fantasya\Message\Unit\PeasantJoyNoneMessage;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Commodity\Potion\PeasantJoy as Potion;

final class PeasantJoy extends AbstractRegionApply
{
	use ActionTrait;

	protected function calculateAmount(): int {
		$resources = $this->apply->Unit()->Region()->Resources();
		$peasants  = $resources[Peasant::class]->Count();
		$available = $this->apply->Count();
		$amount    = (int)ceil($peasants / Potion::PEASANTS);
		if ($amount <= 0) {
			$this->message(PeasantJoyNoneMessage::class, $this->apply->Unit());
		}
		return min($available, $amount);
	}
}
