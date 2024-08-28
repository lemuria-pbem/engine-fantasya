<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Combat\BattleLog;
use Lemuria\Engine\Fantasya\Combat\CombatEffect;
use Lemuria\Engine\Fantasya\Combat\Log\Message\AuraPortalStabilizesMessage;
use Lemuria\Model\Fantasya\Composition\AuraPortal;
use Lemuria\Model\Fantasya\Talent\Magic;

class AstralChaos extends AbstractBattleSpell
{
	public function cast(): int {
		$grade = parent::cast();
		if ($grade > 0) {
			if ($this->hasAuraPortal()) {
				BattleLog::getInstance()->add(new AuraPortalStabilizesMessage());
				return 0;
			}

			$spell  = $this->grade->Spell();
			$level  = $this->calculus->knowledge(Magic::class)->Level();
			$points = $grade * $level;
			$this->grade->Combat()->addEffect(new CombatEffect($spell, $points));
		}
		return $grade;
	}

	/**
	 * Astral Chaos will never fail.
	 */
	protected function modifyReliability(int $grade): int {
		return $grade;
	}

	protected function hasAuraPortal(): bool {
		$region = $this->calculus->Unit()->Region();
		foreach ($region->Treasury() as $unicum) {
			if ($unicum->Composition() instanceof AuraPortal) {
				return true;
			}
		}
		foreach ($region->Estate() as $construction) {
			foreach ($construction->Treasury() as $unicum) {
				if ($unicum->Composition() instanceof AuraPortal) {
					return true;
				}
			}
		}
		return false;
	}
}
