<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Effect\Rumors;
use Lemuria\Engine\Fantasya\Message\Unit\VisitNoMarketMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VisitNoRumorMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VisitNoUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VisitRumorMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Extension\Market;
use Lemuria\Model\Fantasya\Unit;

/**
 * Visit a unit in the market.
 *
 * - BESUCHEN <unit>
 */
final class Visit extends UnitCommand
{
	protected function run(): void {
		$id     = $this->parseId();
		$region = $this->unit->Region();
		$units  = $region->Residents();
		if ($units->has($id)) {
			$hasMarket = false;
			foreach ($region->Estate() as $construction) {
				$extensions = $construction->Extensions();
				if ($extensions->offsetExists(Market::class)) {
					$hasMarket = true;
					if ($construction->Inhabitants()->has($id)) {
						$this->visit($units[$id]);
						return;
					}
				}
			}
			if (!$hasMarket) {
				$this->message(VisitNoMarketMessage::class);
				return;
			}
		}
		$this->message(VisitNoUnitMessage::class)->p($id);
	}

	private function visit(Unit $unit): void {
		$effect = new Rumors(State::getInstance());
		$rumors = Lemuria::Score()->find($effect->setUnit($unit));
		if ($rumors instanceof Rumors && !$this->context->getTurnOptions()->IsSimulation()) {
			foreach ($rumors->Rumors() as $rumor) {
				$this->message(VisitRumorMessage::class)->e($unit)->p($rumor);
			}
		} else {
			$this->message(VisitNoRumorMessage::class)->e($unit);
		}
	}
}
