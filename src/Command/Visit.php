<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Effect\Rumors;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\VisitNoMarketMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VisitNoRumorMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VisitNoUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VisitRumorMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\IdException;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Construction;
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
		try {
			$id = Id::fromId($this->phrase->getParameter());
		} catch (IdException $e) {
			throw new InvalidCommandException($this, previous: $e);
		}

		$region = $this->unit->Region();
		$units  = $region->Residents();
		if ($units->has($id)) {
			$hasMarket = false;
			foreach ($region->Estate() as $construction /* @var Construction $construction */) {
				$extensions = $construction->Extensions();
				if ($extensions->offsetGet(Market::class)) {
					$hasMarket = true;
					if ($construction->Inhabitants()->has($id)) {
						/** @var Unit $unit */
						$unit = $units[$id];
						$this->visit($unit);
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
		if ($rumors instanceof Rumors) {
			foreach ($rumors->Rumors() as $rumor) {
				$this->message(VisitRumorMessage::class)->e($unit)->p($rumor);
			}
		} else {
			$this->message(VisitNoRumorMessage::class)->e($unit);
		}
	}
}
