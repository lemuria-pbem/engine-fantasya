<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use Lemuria\Engine\Fantasya\Factory\ActionTrait;
use Lemuria\Engine\Fantasya\Message\Unit\HorseBlissNoneMessage;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Potion\HorseBliss as Potion;

final class HorseBliss extends AbstractRegionApply
{
	use ActionTrait;

	protected function calculateAmount(): int {
		$resources = $this->apply->Unit()->Region()->Resources();
		$peasants  = $resources[Horse::class]->Count();
		$available = $this->apply->Count();
		$amount    = (int)ceil($peasants / Potion::HORSES);
		if ($amount <= 0) {
			$this->message(HorseBlissNoneMessage::class, $this->apply->Unit());
		}
		return min($available, $amount);
	}
}