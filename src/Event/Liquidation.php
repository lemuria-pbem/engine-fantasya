<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Factory\GiftTrait;
use Lemuria\Engine\Fantasya\Message\Party\LiquidationLostMessage;
use Lemuria\Engine\Fantasya\Message\Party\LiquidationMessage;
use Lemuria\Engine\Fantasya\Message\Party\LiquidationGiftMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LiquidationHeirMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LoseToUnitMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Engine\Fantasya\Statistics\StatisticsTrait;
use Lemuria\Engine\Fantasya\Statistics\Subject;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Unit;

/**
 * Remove empty units at the end of a turn.
 */
final class Liquidation extends AbstractEvent
{
	use GiftTrait;
	use StatisticsTrait;

	public function __construct(State $state) {
		parent::__construct($state, Priority::AFTER);
	}

	protected function run(): void {
		$liquidate = new People();
		foreach (Lemuria::Catalog()->getAll(Domain::PARTY) as $party /* @var Party $party */) {
			Lemuria::Log()->debug('Running Liquidation for Party ' . $party->Id() . '.', ['party' => $party]);
			$units = $party->People();
			$liquidate->clear();
			foreach ($units as $unit /* @var Unit $unit */) {
				if ($unit->Size() <= 0) {
					$liquidate->add($unit);
					$inventory = $unit->Inventory();
					if ($inventory->count() > 0) {
						if (!$this->passOn($inventory, $unit)) {
							if (!$this->giftToOther($inventory, $unit, $party)) {
								$this->message(LiquidationLostMessage::class, $party)->e($unit);
							}
						}
					}
				}
			}
			foreach ($liquidate as $unit /* @var Unit $unit */) {
				Lemuria::Catalog()->reassign($unit);
				$unit->Construction()?->Inhabitants()?->remove($unit);
				$unit->Vessel()?->Passengers()?->remove($unit);
				$unit->Region()->Residents()->remove($unit);
				$units->remove($unit);
				Lemuria::Catalog()->remove($unit);
				$this->message(LiquidationMessage::class, $party)->e($unit);
			}
			$this->placeMetrics(Subject::Units, $party);
			$this->placeMetrics(Subject::People, $party);
		}
	}

	private function passOn(Resources $goods, Unit $unit): bool {
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

	private function giftToOther(Resources $goods, Unit $unit, Party $party): bool {
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
