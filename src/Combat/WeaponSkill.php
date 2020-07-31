<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Combat;

use Lemuria\Model\Lemuria\Ability;
use Lemuria\Model\Lemuria\Factory\BuilderTrait;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Talent\Archery;
use Lemuria\Model\Lemuria\Talent\Bladefighting;
use Lemuria\Model\Lemuria\Talent\Catapulting;
use Lemuria\Model\Lemuria\Talent\Crossbowing;
use Lemuria\Model\Lemuria\Talent\Fistfight;
use Lemuria\Model\Lemuria\Talent\Spearfighting;

/**
 * Helper class for battle configuration of a unit.
 */
class WeaponSkill
{
	use BuilderTrait;

	private static ?Archery $archery = null;

	private static Bladefighting $bladefighting;

	private static Catapulting $catapulting;

	private static Crossbowing $crossbowing;

	private static Fistfight $fistfight;

	private static Spearfighting $spearfighting;

	private Ability $skill;

	private Quantity $weapon;

	/**
	 * Create a weapon skill object.
	 *
	 * @param Ability $skill
	 * @param Quantity $weapon
	 */
	public function __construct(Ability $skill, Quantity $weapon) {
		$this->skill  = $skill;
		$this->weapon = $weapon;
		$this->initTalents();
	}

	/**
	 * Get the skill.
	 *
	 * @return Ability
	 */
	public function Skill(): Ability {
		return $this->skill;
	}

	/**
	 * Get the weapon.
	 *
	 * @return Quantity
	 */
	public function Weapon(): Quantity {
		return $this->weapon;
	}

	/**
	 * Check if weapon skill is suitable for short distance combat.
	 *
	 * @return bool
	 */
	public function isMelee(): bool {
		$talent = $this->skill->Talent();
		return $talent === self::$bladefighting || $talent === self::$spearfighting;
	}

	/**
	 * Check if weapon skill is suitable for long distance combat.
	 *
	 * @return bool
	 */
	public function isDistant(): bool {
		$talent = $this->skill->Talent();
		return $talent === self::$archery || $talent === self::$crossbowing || $talent === self::$catapulting;
	}

	/**
	 * Check if weapon skill is suitable for guards or tax collectors.
	 *
	 * @return bool
	 */
	public function isGuard(): bool {
		return !$this->isUnarmed() && !$this->isSiege();
	}

	/**
	 * Check if weapon skill is suitable for siege.
	 *
	 * @return bool
	 */
	public function isSiege(): bool {
		return $this->skill->Talent() === self::$catapulting;
	}

	/**
	 * Check if weapon skill is unarmed fist fight.
	 *
	 * @return bool
	 */
	public function isUnarmed(): bool {
		return $this->skill->Talent() === self::$fistfight;
	}

	/**
	 * Init static talents.
	 */
	protected function initTalents() {
		if (!self::$archery) {
			self::$archery       = self::createTalent(Archery::class);
			self::$bladefighting = self::createTalent(Bladefighting::class);
			self::$catapulting   = self::createTalent(Catapulting::class);
			self::$fistfight     = self::createTalent(Fistfight::class);
			self::$spearfighting = self::createTalent(Spearfighting::class);
		}
	}
}
