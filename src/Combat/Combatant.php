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
use Lemuria\Model\Fantasya\Weapon;

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

	private ?int $battleRow = null;

	private ?Distribution $distribution = null;

	private ?Weapon $weapon = null;

	private ?WeaponSkill $weaponSkill = null;

	private Attack $attack;

	#[Pure] public function __construct(private Army $army, private Unit $unit) {
		$this->battleRow = Combat::getBattleRow($this->unit);
		$this->attack    = new Attack($this);
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

	public function Weapon(): Weapon {
		return $this->weapon;
	}

	public function WeaponSkill(): WeaponSkill {
		return $this->weaponSkill;
	}

	public function Size(): int {
		return count($this->fighters);
	}

	public function setBattleRow(int $battleRow): Combatant {
		$this->battleRow = $battleRow;
		$this->initWeaponSkill();
		return $this;
	}

	public function setDistribution(Distribution $distribution): Combatant {
		$calculus           = new Calculus($this->unit);
		$this->distribution = $distribution;
		$this->fighters     = array_fill(0, $distribution->Size(), new Fighter($calculus->hitpoints()));
		$this->initWeaponSkill();
		return $this;
	}

	/**
	 * Receive an attack from an assaulting attacker and return the damage done to the defending fighter.
	 */
	public function assault(int $cD, int $fighter, Combatant $attacker, int $cA, int $assaulter): int {
		$health  = $this->fighters[$fighter]->health;
		$damage  = $attacker->attack->perform($cA, $assaulter, $this, $cD, $fighter);
		$health -= $damage > $health ? $health : $damage;
		if ($damage > 0) {
			Lemuria::Log()->debug('Fighter ' . $cA . '/' . $assaulter . ' deals ' . $damage . ' damage to enemy ' . $cD . '/' . $fighter . '.');
		}
		$this->fighters[$fighter]->health = $health;
		return $damage;
	}

	protected function initWeaponSkill(): void {
		if (is_int($this->battleRow) && $this->distribution) {
			$this->weaponSkill = $this->getWeaponSkill();
		}
	}

	protected function getWeaponSkill(): WeaponSkill {
		$calculus = new Calculus($this->unit);
		$isMelee  = $this->battleRow !== CombatModel::BACK;
		foreach ($calculus->weaponSkill() as $weaponSkill) {
			if ($isMelee && $weaponSkill->isMelee() && $this->hasOneWeaponOf($weaponSkill)) {
				return $weaponSkill;
			}
			if (!$isMelee && $weaponSkill->isDistant() && $this->hasOneWeaponOf($weaponSkill)) {
				return $weaponSkill;
			}
			if ($weaponSkill->isUnarmed() && $this->hasOneWeaponOf($weaponSkill)) {
				return $weaponSkill;
			}
		}
		throw new LemuriaException('Unexpected missing weapon skill.');
	}

	protected function hasOneWeaponOf(WeaponSkill $weaponSkill): bool {
		$talent = $weaponSkill->Skill()->Talent()::class;
		if (!isset(WeaponSkill::WEAPONS[$talent])) {
			throw new LemuriaException('WeaponSkill does not define weapons for ' . $talent . '.');
		}
		foreach (WeaponSkill::WEAPONS[$talent] as $weapon /* @var Weapon $weapon */) {
			if ($this->distribution->offsetExists($weapon)) {
				$this->weapon = self::createWeapon($weapon);
				return true;
			}
		}
		return false;
	}
}
