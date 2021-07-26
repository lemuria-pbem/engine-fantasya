<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Unit;

/**
 * Combatants are groups of persons from a unit that fight with the same equipment.
 */
class Combatant
{
	use BuilderTrait;

	private WeaponSkill $weapon;

	public function __construct(private Unit $unit) {
	}

	public function Unit(): Unit {
		return $this->unit;
	}

	public function Weapon(): WeaponSkill {
		return $this->weapon;
	}

	/**
	 * Get the number of persons.
	 */
	#[Pure] public function Size(): int {
		return $this->weapon->Weapon()->Count();
	}

	/**
	 * Set a weapon filter.
	 */
	public function setWeapon(WeaponSkill $weaponSkill): Combatant {
		$this->weapon = $weaponSkill;
		return $this;
	}
}
