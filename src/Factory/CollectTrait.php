<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Factory;

use Lemuria\Engine\Lemuria\Context;
use Lemuria\Engine\Lemuria\Message\Unit\AllocationTakeMessage;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Unit;

trait CollectTrait
{
	private Context $context;

	protected function collectQuantity(Unit $unit, mixed $commodity, int $amount): Quantity {
		$inventory = $unit->Inventory();
		$reserve   = $inventory->offsetExists($commodity) ? $inventory->offsetGet($commodity)->Count() : 0;
		if ($amount > $reserve) {
			$taking       = new Quantity($commodity, $amount - $reserve);
			$resourcePool = $this->context->getResourcePool($unit);
			$resourcePool->take($unit, $taking);
			$this->message(AllocationTakeMessage::class)->i($taking);
		}
		if ($inventory->offsetExists($commodity)) {
			/** @var Quantity $quantity */
			$quantity = $inventory->offsetGet($commodity);
			return $quantity;
		} else {
			return new Quantity($commodity, 0);
		}
	}
}
