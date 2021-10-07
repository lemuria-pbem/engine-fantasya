<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create\RawMaterial;

use Lemuria\Engine\Fantasya\Effect\WorkerLodging;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialCanMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialCannotMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialNoDemandMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialWantsMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\RawMaterial;
use Lemuria\Model\Fantasya\Relation;

/**
 * Special implementation of command MACHEN <raw material> (create raw material) when unit is in an advanced facility.
 *
 * - MACHEN <raw material>
 * - MACHEN <amount> <raw material>
 */
abstract class AbstractDoubleRawMaterial extends BasicRawMaterial
{
	protected bool $hasLodging = false;

	protected function initialize(): void {
		$this->checkForFriendlyLodge();
		parent::initialize();
	}

	protected function run(): void {
		if ($this->hasLodging) {
			$this->doubleResourcesByFacility();
		}
		parent::run();
	}

	/**
	 * Determine the demand.
	 *
	 * @noinspection DuplicatedCode
	 */
	protected function createDemand(): void {
		$resource   = $this->getCommodity();
		$production = 0;
		if ($this->calculus()->isInMaintainedConstruction()) {
			if ($this->hasLodging) {
				$talent          = $this->getRequiredTalent();
				$this->knowledge = $this->calculus()->knowledge($talent->Talent());
				$size            = $this->unit->Size();
				$production      = (int)floor($this->potionBoost($size) * $size * $this->knowledge->Level() / $talent->Level());
			} else {
				$this->addUnusableMessage();
			}
		} else {
			$this->addUnmaintainedMessage();
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

			$material = new Quantity($resource, (int)ceil($quantity->Count() / 2));
			$this->resources->add($material);
		} elseif ($this->hasLodging) {
			$this->message(RawMaterialNoDemandMessage::class)->s($resource);
		}
	}

	abstract protected function addUnusableMessage(): void;

	abstract protected function addUnmaintainedMessage(): void;

	private function checkForFriendlyLodge(): void {
		$needed     = $this->unit->Size();
		$party      = $this->unit->Party();
		$diplomacy  = $party->Diplomacy();
		$dependency = $this->unit->Construction()->Building()->Dependency();
		foreach ($this->unit->Region()->Estate() as $construction /* @var Construction $construction */) {
			if ($construction->Building() === $dependency) {
				$inhabitants = $construction->Inhabitants();
				$owner       = $inhabitants->Owner();
				if ($owner->Party() === $party || $diplomacy->has(Relation::ENTER, $owner)) {
					if ($this->context->getCalculus($owner)->isInMaintainedConstruction()) {
						$lodging    = $this->getLodging($construction);
						$freePlaces = $construction->Size() - $inhabitants->count() - $lodging->Booking();
						if ($freePlaces >= 0) {
							$bookPlaces = min($freePlaces, $needed);
							$lodging->book($bookPlaces);
							$needed -= $bookPlaces;
							if ($needed <= 0) {
								$this->hasLodging = true;
								break;
							}
						}
					}
				}
			}
		}
	}

	private function doubleResourcesByFacility(): void {
		/** @var RawMaterial */
		$material = $this->getCommodity();
		$count    = $this->resources[$material]->Count();
		if ($count > 0) {
			$this->resources->add(new Quantity($material, $count));
		}
	}

	private function getLodging(Construction $construction): WorkerLodging {
		$effect = new WorkerLodging(State::getInstance());
		/** @var WorkerLodging $lodging */
		$lodging = Lemuria::Score()->find($effect->setConstruction($construction));
		if ($lodging) {
			return $lodging;
		}
		Lemuria::Score()->add($effect);
		return $effect;
	}
}
