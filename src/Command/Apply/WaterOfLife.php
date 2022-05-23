<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use Lemuria\Engine\Fantasya\Command\Use\Apply;
use Lemuria\Engine\Fantasya\Factory\ActionTrait;
use Lemuria\Engine\Fantasya\Factory\CollectTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Apply\WaterOfLifeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Apply\WaterOfLifeNoWoodMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Apply\WaterOfLifeOnlyMessage;
use Lemuria\Model\Fantasya\Commodity\Wood;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Commodity\Potion\WaterOfLife as Potion;
use Lemuria\Model\Fantasya\Quantity;

final class WaterOfLife extends AbstractUnitApply
{
	use ActionTrait;
	use BuilderTrait;
	use CollectTrait;

	public function __construct(Apply $apply) {
		parent::__construct($apply);
		$this->context = $apply->Context();
	}

	public function apply(): int {
		$unit     = $this->apply->Unit();
		$amount   = $this->apply->Count();
		$wood     = self::createCommodity(Wood::class);
		$quantity = $this->collectQuantity($unit, $wood, $amount);
		$count    = $quantity->Count();
		if ($count < $amount) {
			if ($count > 0) {
				$this->message(WaterOfLifeOnlyMessage::class, $unit)->i($quantity);
			} else {
				$this->message(WaterOfLifeNoWoodMessage::class, $unit);
			}
			$amount = $count;
		}
		$this->getEffect()->setCount($amount);

		if ($amount > 0) {
			$unit->Inventory()->remove(new Quantity($wood, $amount));
			$saplings = $amount * Potion::SAPLINGS;
			$unit->Region()->Resources()->add(new Quantity($wood, $saplings));
			$this->message(WaterOfLifeMessage::class, $unit)->p($saplings);
		}
		return $amount;
	}
}
