<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Combat\BattleLog;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Engine\Fantasya\Combat\Log\Message\FireballHitMessage;
use Lemuria\Engine\Fantasya\Combat\Rank;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Commodity\Protection\Armor;
use Lemuria\Model\Fantasya\Commodity\Protection\Ironshield;
use Lemuria\Model\Fantasya\Commodity\Protection\Mail;
use Lemuria\Model\Fantasya\Commodity\Protection\Woodshield;
use Lemuria\Model\Fantasya\Talent\Magic;

class Fireball extends AbstractBattleSpell
{
	protected const VICTIMS = 5;

	protected const PROTECTION = [
		Armor::class      => 3,
		Ironshield::class => 5,
		Mail::class       => 1,
		Woodshield::class => 2
	];

	public function cast(): int {
		$damage = parent::cast();
		if ($damage > 0) {
			$level    = $this->calculus->knowledge(Magic::class)->Level();
			$victims  = self::VICTIMS + (int)round(sqrt($level)) - 1;
			$victims  = $this->castOnCombatants($this->victim[BattleRow::Front->value], $damage, $victims);
			$victims  = $this->castOnCombatants($this->victim[BattleRow::Back->value], $damage, $victims);
			$victims  = $this->castOnCombatants($this->victim[BattleRow::Bystander->value], $damage, $victims);
			$this->castOnCombatants($this->victim[BattleRow::Refugee->value], $damage, $victims);
		}
		return $damage;
	}

	/**
	 * @param array<Combatant> $combatants
	 */
	protected function castOnCombatants(Rank $combatants, int $damage, int $victims): int {
		foreach ($combatants as $combatant) {
			if ($victims <= 0) {
				break;
			}
			$damage = $this->calculateDamage($combatant, $damage);
			$size   = min($combatant->Size(), $victims);
			for ($i = 0; $i < $size; $i++) {
				if ($damage > 0) {
					$health                         = $combatant->fighter($i)->health;
					$health                         = max(0, $health - $damage);
					$combatant->fighter($i)->health = $health;
					Lemuria::Log()->debug('Fighter ' . $combatant->getId($i, true) . ' is hit by a Fireball and receives ' . $damage . ' damage.');
					BattleLog::getInstance()->add(new FireballHitMessage($combatant->getId($i, true), $damage));
				}
				$victims--;
			}
		}
		return $victims;
	}

	protected function calculateDamage(Combatant $combatant, int $damage): int {
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
