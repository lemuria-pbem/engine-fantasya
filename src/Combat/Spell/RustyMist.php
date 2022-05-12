<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Engine\Fantasya\Factory\Model\BattleSpellGrade;
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
			$this->rustCombatants($this->victim[BattleRow::FRONT->value], $grade);
			$this->rustCombatants($this->victim[BattleRow::BACK->value], $grade);
			$this->rustCombatants($this->caster[BattleRow::FRONT->value], $grade);
			$this->rustCombatants($this->caster[BattleRow::BACK->value], $grade);
		}
		return $grade;
	}

	protected function rustCombatants(array &$combatants, int $grade): void {
		$n = count($combatants);
		for ($i = 0; $i <= $n; $i++) {
			/** @var Combatant $combatant */
			$combatant = &$combatants[$i];
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
				$rusty = (int)round(0.5 * $size);
				if ($rusty < $size) {
					$newCombatant = $combatant->split($rusty);
					$combatant->Army()->addCombatant($newCombatant);
					$combatants[] = $newCombatant;
					$combatant    = $newCombatant;
					//Lemuria::Log()->debug($who . ' ' . $unit . ' sends ' . $additional . ' persons from combatant ' . $combatant->Id() . ' in ' . $name . ' row to the front as combatant ' . $newCombatant->Id() . '.');
					//BattleLog::getInstance()->add(new EveryoneHasFledMessage());
					//TODO some rust
				} else {
					//Lemuria::Log()->debug($who . ' ' . $unit . ' sends ' . $additional . ' persons from combatant ' . $combatant->Id() . ' in ' . $name . ' row to the front as combatant ' . $newCombatant->Id() . '.');
					//BattleLog::getInstance()->add(new EveryoneHasFledMessage());
					//TODO rust
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
}
