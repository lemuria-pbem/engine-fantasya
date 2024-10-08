<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Effect\Rumors;
use Lemuria\Engine\Fantasya\Factory\ReassignTrait;
use Lemuria\Engine\Fantasya\Factory\VisitTrait;
use Lemuria\Engine\Fantasya\Message\Announcement as Announce;
use Lemuria\Engine\Fantasya\Message\Unit\VisitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VisitNoMarketMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VisitNoRumorMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VisitNoUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VisitRumorMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VisitVisitMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Extension\Market;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Reassignment;

/**
 * Visit a unit in the market or a NPC.
 *
 * - BESUCHEN <unit>
 */
final class Visit extends UnitCommand implements Reassignment
{
	use ReassignTrait;
	use VisitTrait;

	protected function run(): void {
		$id     = $this->parseId();
		$region = $this->unit->Region();
		$units  = $region->Residents();
		if ($units->has($id)) {
			$unit = $units[$id];
			if ($unit->Party()->Type() === Type::NPC) {
				$this->visit($unit);
				return;
			}
			$hasMarket = false;
			foreach ($region->Estate() as $construction) {
				$extensions = $construction->Extensions();
				if ($extensions->offsetExists(Market::class)) {
					$hasMarket = true;
					if ($construction->Inhabitants()->has($id)) {
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
		$this->message(VisitNoUnitMessage::class)->p((string)$id);
	}

	protected function getReassignPhrase(string $old, string $new): ?Phrase {
		return $this->getReassignPhraseForParameter(1, $old, $new);
	}

	private function visit(Unit $unit): void {
		$isSimulation = $this->context->getTurnOptions()->IsSimulation();
		$news         = [];

		$this->addVisitEffect($unit);

		$effect = new Rumors(State::getInstance());
		$rumors = Lemuria::Score()->find($effect->setUnit($unit));
		if ($rumors instanceof Rumors && !$isSimulation) {
			$buzzes = $rumors->getRumorsFor($this->unit);
			if (!$buzzes->isEmpty()) {
				$news[VisitRumorMessage::class] = $buzzes;
			}
		}

		$messages = $this->visitFrom($unit);
		if (!$isSimulation && $messages) {
			$news[VisitMessage::class] = $messages;
		}

		if (empty($news)) {
			$this->message(VisitNoRumorMessage::class)->e($unit);
		} else {
			$this->message(VisitVisitMessage::class)->e($unit);
			$sender    = (string)$unit;
			$recipient = (string)$this->unit;
			foreach ($news as $messageClass => $messages) {
				foreach ($messages as $message) {
					$this->message($messageClass)->p((string)$message)->p($sender, Announce::SENDER)->p($recipient, Announce::RECIPIENT);
				}
			}
		}
	}
}
