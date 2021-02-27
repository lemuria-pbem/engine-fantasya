<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Event;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Action;
use Lemuria\Engine\Lemuria\Factory\GiftTrait;
use Lemuria\Engine\Lemuria\Message\Party\LiquidationLostMessage;
use Lemuria\Engine\Lemuria\Message\Party\LiquidationMessage;
use Lemuria\Engine\Lemuria\Message\Party\LiquidationGiftMessage;
use Lemuria\Engine\Lemuria\Message\Unit\LiquidationHeirMessage;
use Lemuria\Engine\Lemuria\Message\Unit\LoseToUnitMessage;
use Lemuria\Engine\Lemuria\State;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Lemuria\Goods;
use Lemuria\Model\Lemuria\Party;
use Lemuria\Model\Lemuria\People;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Unit;

/**
 * Remove empty units at the end of a turn.
 */
final class Liquidation extends AbstractEvent
{
	use GiftTrait;

	#[Pure] public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Catalog::PARTIES) as $party /* @var Party $party */) {
			Lemuria::Log()->debug('Running Liquidation for Party ' . $party->Id() . '.', ['party' => $party]);
			$units = $party->People();
			foreach ($units as $unit /* @var Unit $unit */) {
				if ($unit->Size() <= 0) {
					$units->remove($unit);
					$inventory = $unit->Inventory();
					if ($inventory->count() > 0) {
						if (!$this->passOn($inventory, $unit)) {
							if (!$this->giftToOther($inventory, $unit, $party)) {
								$this->message(LiquidationLostMessage::class, $party)->e($unit);
							}
						}
					}
					$unit->Construction()?->Inhabitants()?->remove($unit);
					$unit->Vessel()?->Passengers()?->remove($unit);
					$unit->Region()->Residents()->remove($unit);
					Lemuria::Catalog()->remove($unit);
					$this->message(LiquidationMessage::class, $party)->e($unit);
				}
			}
		}
	}

	private function passOn(Goods $goods, Unit $unit): bool {
		$heirs = $this->context->getIntelligence($unit->Region())->getHeirs($unit);
		$heir  = $heirs->get();
		if ($heir) {
			$inventory = $heir->Inventory();
			foreach ($goods as $quantity/* @var Quantity $quantity */) {
				$inventory->add($quantity);
			}
			$this->message(LiquidationHeirMessage::class, $heir)->e($unit);
			return true;
		}
		return false;
	}

	private function giftToOther(Goods $goods, Unit $unit, Party $party): bool {
		$heirs = $this->context->getIntelligence($unit->Region())->getHeirs($unit, false);
        foreach ($goods as $quantity /* @var Quantity $quantity */) {
            $heir = $this->giftToRandom($heirs, $quantity);
            if (!$heir) {
                return false;
            }
            $this->message(LoseToUnitMessage::class, $heir)->e($unit)->i($quantity);
        }
		$this->message(LiquidationGiftMessage::class, $party)->e($unit);
		return true;
	}
}
