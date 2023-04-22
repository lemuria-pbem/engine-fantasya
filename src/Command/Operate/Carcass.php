<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Operate;

use Lemuria\Model\Fantasya\Composition\Carcass as CarcassModel;
use Lemuria\Model\Fantasya\Quantity;

final class Carcass extends AbstractOperate
{
	use BurnTrait;

	private const DISINTEGRATE = 6;

	public function take(): void {
		$carcass = $this->getCarcass();
		$loot    = $carcass->Inventory();
		if ($loot->isEmpty()) {
			//TODO nothing
			return;
		}

		$inventory = $this->unit->Inventory();
		$take      = [];
		foreach ($carcass->Inventory() as $item) {
			$take[] = $item;
		}
		foreach ($take as $item) {
			$loot->remove($item);
			$inventory->add(new Quantity($item->Commodity(), $item->Count()));
			//TODO taken
		}
	}

	private function getCarcass(): CarcassModel {
		/** @var CarcassModel $carcass */
		$carcass = $this->operator->Unicum()->Composition();
		return $carcass;
	}
}
