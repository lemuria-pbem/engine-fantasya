<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Unit;

trait CamouflageTrait
{
	use ContextTrait;

	/**
	 * Check recipients' acceptance for foreign parties.
	 */
	private function checkVisibility(Calculus $calculus, Unit $target): bool {
		$unit  = $calculus->Unit();
		$party = $unit->Party();
		$other = $target->Party();
		if ($other === $party) {
			return true;
		}
		if ($calculus->canDiscover($target)) {
			return true;
		}
		return !$this->context->getTurnOptions()->IsSimulation() && $other->Diplomacy()->has(Relation::PERCEPTION, $unit);
	}
}
