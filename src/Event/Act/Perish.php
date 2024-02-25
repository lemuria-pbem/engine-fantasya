<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use function Lemuria\randElement;
use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Effect\PerishEffect;
use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Factory\Namer\RaceNamer;
use Lemuria\Engine\Fantasya\Message\Unit\Act\PerishMemberMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Act\PerishMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Act\PerishOneMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Distribution;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

/**
 * Old, ill or wounded individuals may perish and create a Cadaver unicum.
 */
class Perish implements Act
{
	use ActTrait;
	use MessageTrait;

	public function act(): static {
		Lemuria::Log()->debug('In ' . $this->unit . ' one member perishes.');
		$size = $this->unit->Size();
		if ($size > 1) {
			$this->unit->setSize($size - 1);
			$unit = $this->createPerishUnit();
			$this->addEffect($unit);
		} else {
			$this->unit->setHealth(0.0);
			$this->message(PerishMessage::class, $this->unit);
			$this->addEffect($this->unit);
		}
		return $this;
	}

	private function createPerishUnit(): Unit {
		$party  = $this->unit->Party();
		$region = $this->unit->Region();
		$race   = $this->unit->Race();

		$unit = new Unit();
		$unit->setId(Lemuria::Catalog()->nextId(Domain::Unit));
		$unit->setRace($race)->setSize(1)->setHealth(0.0)->setBattleRow(BattleRow::Refugee);
		$party->People()->add($unit);
		$region->Residents()->add($unit);
		$namer = new RaceNamer();
		$namer->name($unit);

		$calculus      = new Calculus($this->unit);
		$distributions = $calculus->inventoryDistribution();
		if (!empty($distributions)) {
			/** @var Distribution $distribution */
			$distribution = randElement($distributions);
			$from         = $this->unit->Inventory();
			$to           = $unit->Inventory();
			foreach ($distribution as $quantity) {
				$from->remove($quantity);
				$to->add(new Quantity($quantity->Commodity(), $quantity->Count()));
			}
		}

		$this->message(PerishMemberMessage::class, $this->unit);
		$this->message(PerishOneMessage::class, $unit)->s($race)->e($this->unit);

		return $unit;
	}

	private function addEffect(Unit $unit): void {
		$state  = State::getInstance();
		$effect = new PerishEffect($state);
		if (!Lemuria::Score()->find($effect->setUnit($unit))) {
			Lemuria::Score()->add($effect);
			$state->injectIntoTurn($effect);
		}
	}
}
