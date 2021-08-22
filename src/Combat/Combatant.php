<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Factory\Model\Distribution;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat as CombatModel;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Unit;

/**
 * Combatants are groups of persons from a unit that fight with the same equipment.
 */
class Combatant
{
	use BuilderTrait;

	/**
	 * @var Fighter[]
	 */
	public array $fighters;

	private int $battleRow;

	private Distribution $distribution;

	private WeaponSkill $weapon;

	public static function getWeaponSkill(Unit $unit, int $battleRow): WeaponSkill {
		$calculus = new Calculus($unit);
		$isMelee  = $battleRow !== CombatModel::BACK;
		//TODO Check for available weapon in inventory.
		foreach ($calculus->weaponSkill() as $weaponSkill) {
			if ($isMelee && $weaponSkill->isMelee() || !$isMelee && $weaponSkill->isDistant() || $weaponSkill->isUnarmed()) {
				return $weaponSkill;
			}
		}
		throw new LemuriaException('Unexpected missing weapon skill.');
	}

	#[Pure] public function __construct(private Army $army, private Unit $unit) {
		$this->battleRow = Combat::getBattleRow($this->unit);
	}

	public function Army(): Army {
		return $this->army;
	}

	public function Unit(): Unit {
		return $this->unit;
	}

	public function BattleRow(): int {
		return $this->battleRow;
	}

	public function Distribution(): Distribution {
		return $this->distribution;
	}

	public function Weapon(): WeaponSkill {
		return $this->weapon;
	}

	public function Size(): int {
		return count($this->fighters);
	}

	public function setBattleRow(int $battleRow): Combatant {
		$this->battleRow = $battleRow;
		return $this;
	}

	public function setDistribution(Distribution $distribution): Combatant {
		$calculus           = new Calculus($this->unit);
		$this->distribution = $distribution;
		$this->fighters     = array_fill(0, $distribution->Size(), new Fighter($calculus->hitpoints()));
		return $this;
	}

	public function setWeapon(WeaponSkill $weaponSkill): Combatant {
		$this->weapon = $weaponSkill;
		return $this;
	}

	/**
	 * Receive an attack from an assaulting attacker and return the damage done to the defending fighter.
	 */
	public function assault(int $cD, int $fighter, Combatant $attacker, int $cA, int $assaulter): int {
		$health = $this->fighters[$fighter]->health;

		$damage = rand(0, 10); //TODO: Attack!

		if ($damage > 0) {
			$health                          -= $damage;
			$this->fighters[$fighter]->health = $health;
			Lemuria::Log()->debug('Fighter ' . $cA . '/' . $assaulter . ' deals ' . $damage . ' damage to enemy ' . $cD . '/' . $fighter . '.');
		}

		return $damage;
	}
}
