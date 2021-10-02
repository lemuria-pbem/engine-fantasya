<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use Lemuria\Engine\Fantasya\Effect\CabinLodging;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialCanMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialCannotMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialNoDemandMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialWantsMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SawmillWoodUnusable;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Building\Cabin;
use Lemuria\Model\Fantasya\Commodity\Wood;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Relation;

/**
 * Special implementation of command MACHEN Holz (create wood) when unit is in a Sawmill.
 *
 * - MACHEN Holz
 * - MACHEN <amount> Holz
 */
final class SawmillWood extends RawMaterial
{
	private bool $hasCabin = false;

	protected function initialize(): void {
		$this->checkForFriendlyCabin();
		parent::initialize();
	}

	protected function run(): void {
		if ($this->hasCabin) {
			$this->doubleResourcesBySawmill();
		}
		parent::run();
	}

	/**
	 * Determine the demand.
	 *
	 * @noinspection DuplicatedCode
	 */
	protected function createDemand(): void {
		$wood = $this->getCommodity();
		if ($this->hasCabin) {
			$woodchopping    = $this->getRequiredTalent();
			$this->knowledge = $this->calculus()->knowledge($woodchopping->Talent());
			$size            = $this->unit->Size();
			$production      = (int)floor($this->potionBoost($size) * $size * $this->knowledge->Level() / $woodchopping->Level());
		} else {
			$production = 0;
			$this->message(SawmillWoodUnusable::class)->s($wood);
		}
		$this->production = $this->reduceByWorkload($production);

		if ($this->production > 0) {
			if (count($this->phrase) === 2) {
				$this->demand = (int)$this->phrase->getParameter();
				if ($this->demand <= $this->production) {
					$this->production = (int)$this->demand;
					$quantity = new Quantity($this->getCommodity(), $this->production);
					$this->message(RawMaterialWantsMessage::class)->i($quantity);
				} else {
					$quantity = new Quantity($this->getCommodity(), $this->production);
					$this->message(RawMaterialCannotMessage::class)->i($quantity);
				}
			} else {
				$quantity = new Quantity($this->getCommodity(), $this->production);
				$this->message(RawMaterialCanMessage::class)->i($quantity);
			}
			$this->addToWorkload($this->production);

			$trees = new Quantity($wood, (int)ceil($quantity->Count() / 2));
			$this->resources->add($trees);
		} elseif ($this->hasCabin) {
			$this->message(RawMaterialNoDemandMessage::class)->s($wood);
		}
	}

	private function checkForFriendlyCabin(): void {
		$needed    = $this->unit->Size();
		$party     = $this->unit->Party();
		$diplomacy = $party->Diplomacy();
		foreach ($this->unit->Region()->Estate() as $construction /* @var Construction $construction */) {
			if ($construction->Building() instanceof Cabin) {
				$inhabitants = $construction->Inhabitants();
				$owner       = $inhabitants->Owner();
				if ($owner->Party() === $party || $diplomacy->has(Relation::ENTER, $owner)) {
					$lodging    = $this->getLodging($construction);
					$freePlaces = $construction->Size() - $inhabitants->count() - $lodging->Booking();
					if ($freePlaces >= 0) {
						$bookPlaces = min($freePlaces, $needed);
						$lodging->book($bookPlaces);
						$needed -= $bookPlaces;
						if ($needed <= 0) {
							$this->hasCabin = true;
							break;
						}
					}
				}
			}
		}
	}

	private function doubleResourcesBySawmill(): void {
		/** @var Wood $wood */
		$wood  = $this->getCommodity();
		$count = $this->resources[$wood]->Count();
		if ($count > 0) {
			$this->resources->add(new Quantity($wood, $count));
		}
	}

	private function getLodging(Construction $construction): CabinLodging {
		$effect = new CabinLodging(State::getInstance());
		/** @var CabinLodging $lodging */
		$lodging = Lemuria::Score()->find($effect->setConstruction($construction));
		if ($lodging) {
			return $lodging;
		}
		Lemuria::Score()->add($effect);
		return $effect;
	}
}
