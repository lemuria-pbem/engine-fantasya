<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Census;
use Lemuria\Engine\Fantasya\Outlook;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Unit;

trait CamouflageTrait
{
	use ContextTrait;

	/**
	 * Check target unit's visibility.
	 */
	private function checkVisibility(Unit $unit, Unit $target): bool {
		$party = $unit->Party();
		$other = $target->Party();
		if ($other === $party) {
			return true;
		}
		$outlook     = new Outlook(new Census($party));
		$apparitions = $outlook->Apparitions($target->Region());
		if ($apparitions->has($target->Id())) {
			return true;
		}
		return !$this->context->getTurnOptions()->IsSimulation() && $other->Diplomacy()->has(Relation::PERCEPTION, $unit);
	}
}
