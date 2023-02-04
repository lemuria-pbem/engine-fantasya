<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Apply\HorseBlissNoneMessage;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Potion\HorseBliss as Potion;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;

final class HorseBlissBreed extends AbstractConstructionApply
{
	use BuilderTrait;
	use MessageTrait;

	protected function calculateAmount(): int {
		$horses = 0;
		$horse  = self::createCommodity(Horse::class);
		foreach ($this->apply->Unit()->Construction()->Inhabitants() as $unit) {
			$inventory = $unit->Inventory();
			$horses += $inventory[$horse]->Count();
		}
		$available = $this->apply->Count();
		$amount    = (int)ceil($horses / Potion::HORSES);
		if ($amount <= 0) {
			$this->message(HorseBlissNoneMessage::class, $this->apply->Unit());
		}
		return min($available, $amount);
	}
}
