<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Model\Fantasya\Commodity\Weapon\Fists;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Talent\Fistfight;
use Lemuria\Model\Fantasya\Unit;

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
