<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Message\Unit\AllocationTakeMessage;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

trait CollectTrait
{
	protected Context $context;

	protected function collectQuantity(Unit $unit, mixed $commodity, int $amount): Quantity {
		$inventory = $unit->Inventory();
		$reserve   = $inventory->offsetExists($commodity) ? $inventory->offsetGet($commodity)->Count() : 0;
		if ($amount > $reserve) {
			$taking       = new Quantity($commodity, $amount - $reserve);
			$resourcePool = $this->context->getResourcePool($unit);
			$taking       = $resourcePool->take($unit, $taking);
			$this->message(AllocationTakeMessage::class, $unit)->i($taking);
		}
		$reserve = $inventory->offsetExists($commodity) ? $inventory->offsetGet($commodity)->Count() : 0;
		return new Quantity($commodity, $reserve);
	}
}
