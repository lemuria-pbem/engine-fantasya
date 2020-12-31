<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Calculus;
use Lemuria\Model\Lemuria\Commodity\Weapon\Fists;
use Lemuria\Model\Lemuria\Factory\BuilderTrait;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Talent\Fistfight;
use Lemuria\Model\Lemuria\Unit;

/**
 * Combatants are groups of persons from a unit that fight with the same equipment.
 */
class Combatant
{
	use BuilderTrait;

	private int $size;

	private WeaponSkill $weapon;

	/**
	 * Create a combatant for a unit.
	 */
	#[Pure] public function __construct(private Unit $unit) {
		$this->size = $unit->Size();
	}

	#[Pure] public function Unit(): Unit {
		return $this->unit;
	}

	/**
	 * Get the weapon skill.
	 */
	public function Weapon(): WeaponSkill {
		if (!$this->weapon) {
			$calculus     = new Calculus($this->unit);
			$fists        = new Quantity(self::createCommodity(Fists::class), $this->unit->Size());
			$this->weapon = new WeaponSkill($calculus->knowledge(Fistfight::class), $fists);
		}
		return $this->weapon;
	}

	/**
	 * Get the number of persons.
	 */
	#[Pure] public function Size(): int {
		return $this->size;
	}

	/**
	 * Set a weapon filter.
	 */
	public function setWeapon(WeaponSkill $weaponSkill): Combatant {
		$this->weapon = $weaponSkill;
		return $this->calculateSize();
	}

	/**
	 * Calculates the size based on the filters set.
	 */
	protected function calculateSize(): Combatant {
		$this->size = $this->Weapon()->Weapon()->Count();

		return $this;
	}
}
