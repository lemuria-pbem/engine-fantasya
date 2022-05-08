<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Effect\WorkerLodging;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Relation;

trait LodgingTrait
{
	/**
	 * Get a lodging for this unit.
	 */
	protected function getLodging(): ?WorkerLodging {
		$party      = $this->unit->Party();
		$diplomacy  = $party->Diplomacy();
		$dependency = $this->unit->Construction()->Building()->Dependency();
		foreach ($this->unit->Region()->Estate()->getIterator() as $construction /* @var Construction $construction */) {
			if ($construction->Building() === $dependency) {
				$inhabitants = $construction->Inhabitants();
				$owner       = $inhabitants->Owner();
				if ($owner && ($owner->Party() === $party || $diplomacy->has(Relation::ENTER, $owner))) {
					$calculus = new Calculus($owner);
					if ($calculus->isInMaintainedConstruction()) {
						$lodging = $this->getWorkerLodging($construction);
						if ($lodging->hasBooked($this->unit)) {
							return $lodging;
						}
					}
				}
			}
		}
		return null;
	}

	/**
	 * Book a lodging.
	 */
	protected function bookLodging(): bool {
		$party      = $this->unit->Party();
		$diplomacy  = $party->Diplomacy();
		$dependency = $this->unit->Construction()->Building()->Dependency();
		$lodge      = null;
		$space      = 0;
		foreach ($this->unit->Region()->Estate() as $construction /* @var Construction $construction */) {
			if ($construction->Building() === $dependency) {
				$inhabitants = $construction->Inhabitants();
				$owner       = $inhabitants->Owner();
				if ($owner && ($owner->Party() === $party || $diplomacy->has(Relation::ENTER, $owner))) {
					$calculus = new Calculus($owner);
					if ($calculus->isInMaintainedConstruction()) {
						$lodging = $this->getWorkerLodging($construction);
						if ($lodging->hasBooked($this->unit)) {
							return true;
						}

						$free = $lodging->Space();
						if ($free > $space) {
							$lodge = $lodging;
							$space = $free;
						}
					}
				}
			}
		}
		return $lodge && $lodge->book($this->unit)->hasSpace($this->unit);
	}

	private function getWorkerLodging(Construction $construction): WorkerLodging {
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
