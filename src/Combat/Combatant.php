<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Combat;

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

	private Unit $unit;

	private int $size;

	private WeaponSkill $weapon;

	/**
	 * Create a combatant for a unit.
	 *
	 * @param Unit $unit
	 */
	public function __construct(Unit $unit) {
		$this->unit = $unit;
		$this->size = $unit->Size();
	}

	/**
	 * Get the unit.
	 *
	 * @return Unit
	 */
	public function Unit(): Unit {
		return $this->unit;
	}

	/**
	 * Get the weapon skill.
	 *
	 * @return WeaponSkill
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
	 *
	 * @return int
	 */
	public function Size(): int {
		return $this->size;
	}

	/**
	 * Set a weapon filter.
	 *
	 * @param WeaponSkill $weaponSkill
	 * @return self
	 */
	public function setWeapon(WeaponSkill $weaponSkill): self {
		$this->weapon = $weaponSkill;
		return $this->calculateSize();
	}

	/**
	 * Calculates the size based on the filters set.
	 *
	 * @return Combatant
	 */
	protected function calculateSize(): self {
		$this->size = $this->Weapon()->Weapon()->Count();

		return $this;
	}
}
