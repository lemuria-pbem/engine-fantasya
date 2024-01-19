<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Effect\Rumors;
use Lemuria\Engine\Fantasya\Effect\VisitEffect;
use Lemuria\Engine\Fantasya\Factory\ReassignTrait;
use Lemuria\Engine\Fantasya\Message\Unit\VisitNoMarketMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VisitNoRumorMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VisitNoUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VisitRumorMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Extension\Market;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Reassignment;

/**
 * Visit a unit in the market.
 *
 * - BESUCHEN <unit>
 */
final class Visit extends UnitCommand implements Reassignment
{
	use ReassignTrait;

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

	protected function getReassignPhrase(string $old, string $new): ?Phrase {
		return $this->getReassignPhraseForParameter(1, $old, $new);
	}

	private function visit(Unit $unit): void {
		$score = Lemuria::Score();
		$state = State::getInstance();

		$effect   = new VisitEffect($state);
		$existing = $score->find($effect->setUnit($unit));
		if ($existing instanceof VisitEffect) {
			$effect = $existing;
		} else {
			$score->add($effect);
		}
		$effect->Parties()->add($this->unit->Party());

		$effect = new Rumors($state);
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
