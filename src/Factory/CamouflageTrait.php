<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Census;
use Lemuria\Engine\Fantasya\Effect\GriffinAttack;
use Lemuria\Engine\Fantasya\Outlook;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Griffin;
use Lemuria\Model\Fantasya\Party\Type;
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
		if ($target->Construction() || $target->Vessel()) {
			return true;
		}
		$outlook     = new Outlook(new Census($party));
		$apparitions = $outlook->getApparitions($target->Region());
		if ($apparitions->has($target->Id())) {
			return true;
		}
		return !$this->context->getTurnOptions()->IsSimulation() && $other->Diplomacy()->has(Relation::PERCEPTION, $unit);
	}

	private function checkAttackVisibility(Unit $unit, Unit $target): bool {
		$attacker = $unit->Party()->Type();
		$defender = $target->Party()->Type();
		if ($attacker === Type::Monster) {
			if ($defender === Type::Monster) {
				return true;
			}
			if ($unit->Race() instanceof Griffin) {
				$effect = new GriffinAttack(State::getInstance());
				$effect = Lemuria::Score()->find($effect->setRegion($unit->Region()));
				if ($effect instanceof GriffinAttack && $unit === $effect->Griffins()) {
					return true;
				}
			}
		}
		return $this->checkVisibility($unit, $target);
	}
}
