<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Combat\BattleLog;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Engine\Fantasya\Combat\Log\Message\FireballHitMessage;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat;
use Lemuria\Model\Fantasya\Commodity\Armor;
use Lemuria\Model\Fantasya\Commodity\Ironshield;
use Lemuria\Model\Fantasya\Commodity\Mail;
use Lemuria\Model\Fantasya\Commodity\Woodshield;
use Lemuria\Model\Fantasya\Talent\Magic;
use Lemuria\Model\Fantasya\Unit;

class Fireball extends AbstractBattleSpell
{
	protected const VICTIMS = 5;

	protected const PROTECTION = [
		Armor::class      => 3,
		Ironshield::class => 5,
		Mail::class       => 1,
		Woodshield::class => 2
	];

	public function cast(Unit $unit): int {
		$damage = parent::cast($unit);
		if ($damage > 0) {
			$calculus = new Calculus($unit);
			$level    = $calculus->knowledge(Magic::class)->Level();
			$victims  = self::VICTIMS + (int)round(sqrt($level)) - 1;
			$victims  = $this->castOnCombatants($this->victim[Combat::FRONT], $damage, $victims);
			$victims  = $this->castOnCombatants($this->victim[Combat::BACK], $damage, $victims);
			$victims  = $this->castOnCombatants($this->victim[Combat::BYSTANDER], $damage, $victims);
			$this->castOnCombatants($this->victim[Combat::REFUGEE], $damage, $victims);
		}
		return $damage;
	}

	/**
	 * @param Combatant[] $combatants
	 */
	protected function castOnCombatants(array &$combatants, int $damage, int $victims): int {
		foreach (array_keys($combatants) as $i) {
			if ($victims <= 0) {
				break;
			}
			$combatant = &$combatants[$i];
			$damage    = $this->calculateDamage($combatant, $damage);
			$size      = min($combatant->Size(), $victims);
			for ($i = 0; $i < $size; $i++) {
				if ($damage > 0) {
					$health                          = $combatant->fighters[$i]->health;
					$health                          = max(0, $health - $damage);
					$combatant->fighters[$i]->health = $health;
					Lemuria::Log()->debug('Fighter ' . $combatant->getId($i) . ' is hit by a Fireball and receives ' . $damage . ' damage.');
					BattleLog::getInstance()->add(new FireballHitMessage($combatant->getId($i), $damage));
				}
				$victims--;
			}
		}
		return $victims;
	}

	#[Pure] protected function calculateDamage(Combatant $combatant, int $damage): int {
		$armor  = $combatant->Armor();
		$shield = $combatant->Shield();
		if ($armor && isset(self::PROTECTION[$armor::class])) {
			$damage -= self::PROTECTION[$armor::class];
		}
		if ($shield && isset(self::PROTECTION[$shield::class])) {
			$damage -= self::PROTECTION[$shield::class];
		}
		$minimum = $armor && $shield ? 0 : 1;
		return max($minimum, $damage);
	}
}