<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use function Lemuria\randInt;
use Lemuria\Engine\Fantasya\Combat\BattleLog;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Engine\Fantasya\Combat\Log\Message\CombatantWeaponDegradedMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\CombatantWeaponSplitMessage;
use Lemuria\Engine\Fantasya\Combat\Rank;
use Lemuria\Engine\Fantasya\Factory\Model\BattleSpellGrade;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Protection\Armor;
use Lemuria\Model\Fantasya\Commodity\Protection\Ironshield;
use Lemuria\Model\Fantasya\Commodity\Protection\Mail;
use Lemuria\Model\Fantasya\Commodity\Weapon\Battleaxe;
use Lemuria\Model\Fantasya\Commodity\Weapon\Sword;
use Lemuria\Model\Fantasya\Factory\RepairableCatalog;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Repairable;
use Lemuria\Model\Fantasya\Unit;

class RustyMist extends AbstractBattleSpell
{
	protected final const AFFECTED = [
		Battleaxe::class => true, Sword::class => true,
		Armor::class => true, Ironshield::class => true, Mail::class => true
	];

	private RepairableCatalog $repairables;

	public function __construct(BattleSpellGrade $grade) {
		parent::__construct($grade);
		$this->repairables = new RepairableCatalog();
	}

	public function cast(Unit $unit): int {
		$grade = parent::cast($unit);
		if ($grade > 0) {
			$this->rustCombatants($this->victim[BattleRow::Front->value], $grade);
			$this->rustCombatants($this->victim[BattleRow::Back->value], $grade);
			$this->rustCombatants($this->caster[BattleRow::Front->value], $grade);
			$this->rustCombatants($this->caster[BattleRow::Back->value], $grade);
		}
		return $grade;
	}

	protected function rustCombatants(Rank $combatants, int $grade): void {
		foreach ($combatants as $combatant) {
			/** @var Commodity $weapon */
			$weapon      = $combatant->Weapon();
			$rustyWeapon = null;
			if (isset(self::AFFECTED[$weapon::class])) {
				$rustyWeapon = $this->repairables->getRepairable($weapon);
			}
			/** @var Commodity $protection */
			$protection      = $combatant->Armor();
			$rustyProtection = null;
			if ($protection && isset(self::AFFECTED[$protection::class])) {
				$rustyProtection = $this->repairables->getRepairable($protection);
			}
			/** @var Commodity $shield */
			$shield      = $combatant->Shield();
			$rustyShield = null;
			if ($shield && isset(self::AFFECTED[$shield::class])) {
				$rustyShield = $this->repairables->getRepairable($shield);
			}

			if ($rustyWeapon || $rustyProtection || $rustyShield) {
				$size  = $combatant->Size();
				$rusty = $this->calculateRusty($grade, $size);
				if ($rusty < $size) {
					$newCombatant = $combatant->split($rusty);
					$combatant->Army()->addCombatant($newCombatant);
					$combatants[] = $newCombatant;
					BattleLog::getInstance()->add(new CombatantWeaponSplitMessage($combatant, $rusty, $newCombatant));
					Lemuria::Log()->debug('Combatant ' . $combatant->Id() . ' sends ' . $rusty . ' fighters to new combatant ' . $newCombatant->Id() . '.');
					BattleLog::getInstance()->add(new CombatantWeaponDegradedMessage($newCombatant));
					Lemuria::Log()->debug('Weapon ' . $weapon . ' of combatant ' . $newCombatant->Id() . ' degrades.');
					$combatant = $newCombatant;
				} else {
					Lemuria::Log()->debug('Weapon ' . $weapon . ' of combatant ' . $combatant->Id() . ' degrades.');
					BattleLog::getInstance()->add(new CombatantWeaponDegradedMessage($combatant));
				}
				if ($rustyWeapon) {
					$this->replace($combatant, $weapon, $rustyWeapon);
				}
				if ($rustyProtection) {
					$this->replace($combatant, $protection, $rustyProtection);
				}
				if ($rustyShield) {
					$this->replace($combatant, $shield, $rustyShield);
				}
			}
		}
	}

	private function replace(Combatant $combatant, Commodity $from, Repairable $to): void {
		/** @var Commodity $commodity */
		$commodity = $to;
		$unit      = $combatant->Unit();
		$inventory = $unit->Inventory();
		unset($inventory[$from]);
		$inventory->add(new Quantity($commodity, $combatant->Size()));
		$combatant->degradeGear($from, $commodity);
	}

	private function calculateRusty(int $grade, int $size): int {
		$factor = match ($grade) {
			1       => 0.3 + randInt(-5, 3) / 100,
			2       => 0.5 + randInt(-10, 5) / 100,
			default => min(1.0, $grade / ++$grade + randInt(-3, 3) / 100)
		};
		return (int)round($factor * $size);
	}
}
