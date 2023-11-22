<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Operate;

use Lemuria\Engine\Fantasya\Effect\UnicumRead;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\CarcassMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\CarcassNothingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\CarcassNotMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\CarcassOnlyMessage;
use Lemuria\Model\Fantasya\Commodity\Griffin;
use Lemuria\Model\Fantasya\Commodity\Monster\Bear;
use Lemuria\Model\Fantasya\Composition\Carcass as CarcassModel;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Resources;

final class Carcass extends AbstractOperate
{
	use BurnTrait;

	public final const int DISINTEGRATE = 6;

	/**
	 * @type array<string, true>
	 */
	public final const array WITH_TROPHY = [Bear::class => true, Griffin::class => true];

	public function take(): void {
		$unicum  = $this->operator->Unicum();
		$carcass = $this->getCarcass();
		$loot    = $carcass->Inventory();
		if ($this->context->getTurnOptions()->IsSimulation() || $loot->isEmpty()) {
			$this->message(CarcassNothingMessage::class, $this->unit)->s($carcass)->e($unicum);
			return;
		}

		$quantity = $this->parseQuantity();
		$wanted   = $quantity?->Count();
		if ($quantity && $wanted <= 0) {
			throw new InvalidCommandException((string)$this->operator->Phrase());
		}

		$inventory = $this->unit->Inventory();
		$take      = [];
		foreach ($carcass->Inventory() as $item) {
			$commodity = $item->Commodity();
			if ($quantity && $commodity !== $quantity->Commodity()) {
				continue;
			}
			$take[] = $quantity ? new Quantity($commodity, min($item->Count(), $wanted)) : $item;
		}
		if (empty($take)) {
			$commodity = $quantity->Commodity();
			$this->message(CarcassNotMessage::class, $this->unit)->s($carcass)->e($unicum)->s($commodity, CarcassNotMessage::ITEM);
			return;
		}

		foreach ($take as $item) {
			$count = $item->Count();
			$taken = new Quantity($item->Commodity(), $count);
			$loot->remove($item);
			$inventory->add($taken);
			if ($quantity && $wanted > $count) {
				$this->message(CarcassOnlyMessage::class, $this->unit)->s($carcass)->e($unicum)->i($taken);
			} else {
				$this->message(CarcassMessage::class, $this->unit)->s($carcass)->e($unicum)->i($taken);
			}
		}
	}

	public function read(): UnicumRead {
		$effect    = parent::read();
		$carcass   = $this->getCarcass();
		$inventory = new Resources();
		$effect->setInventory($this->operator->Unicum(), $inventory->fill($carcass->Inventory()));
		return $effect;
	}

	private function getCarcass(): CarcassModel {
		/** @var CarcassModel $carcass */
		$carcass = $this->operator->Unicum()->Composition();
		return $carcass;
	}

	private function parseQuantity(): ?Quantity {
		$phrase = $this->operator->Phrase();
		$n      = $phrase->count();
		$i      = $this->operator->ArgumentIndex();
		if ($i <= $n) {
			$parameter = $phrase->getParameter($i);
			$amount    = (int)$parameter;
			if ((string)$amount === $parameter) {
				$commodity = $phrase->getLine(++$i);
			} else {
				$amount    = PHP_INT_MAX;
				$commodity = $phrase->getLine($i);
			}
			return new Quantity($this->context->Factory()->commodity($commodity), $amount);
		}
		return null;
	}
}
