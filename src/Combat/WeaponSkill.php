<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Model\Lemuria\Ability;
use Lemuria\Model\Lemuria\Factory\BuilderTrait;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Talent;
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

	private static ?Talent $archery = null;

	private static Talent $bladefighting;

	private static Talent $catapulting;

	private static Talent $crossbowing;

	private static Talent $fistfight;

	private static Talent $spearfighting;

	public function __construct(private Ability $skill, private Quantity $weapon) {
		$this->initTalents();
	}

	#[Pure] public function Skill(): Ability {
		return $this->skill;
	}

	#[Pure] public function Weapon(): Quantity {
		return $this->weapon;
	}

	/**
	 * Check if weapon skill is suitable for short distance combat.
	 */
	#[Pure] public function isMelee(): bool {
		$talent = $this->skill->Talent();
		return $talent === self::$bladefighting || $talent === self::$spearfighting;
	}

	/**
	 * Check if weapon skill is suitable for long distance combat.
	 */
	#[Pure] public function isDistant(): bool {
		$talent = $this->skill->Talent();
		return $talent === self::$archery || $talent === self::$crossbowing || $talent === self::$catapulting;
	}

	/**
	 * Check if weapon skill is suitable for guards or tax collectors.
	 */
	#[Pure] public function isGuard(): bool {
		return !$this->isUnarmed() && !$this->isSiege();
	}

	/**
	 * Check if weapon skill is suitable for siege.
	 */
	#[Pure] public function isSiege(): bool {
		return $this->skill->Talent() === self::$catapulting;
	}

	/**
	 * Check if weapon skill is unarmed fist fight.
	 */
	#[Pure] public function isUnarmed(): bool {
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
			self::$crossbowing   = self::createTalent(Crossbowing::class);
			self::$fistfight     = self::createTalent(Fistfight::class);
			self::$spearfighting = self::createTalent(Spearfighting::class);
		}
	}
}
