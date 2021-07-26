<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Archery;
use Lemuria\Model\Fantasya\Talent\Bladefighting;
use Lemuria\Model\Fantasya\Talent\Catapulting;
use Lemuria\Model\Fantasya\Talent\Crossbowing;
use Lemuria\Model\Fantasya\Talent\Fistfight;
use Lemuria\Model\Fantasya\Talent\Spearfighting;
use Lemuria\Model\Fantasya\Talent\Stoning;

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

	private static Talent $stoning;

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
	 * Check if weapon skill is unarmed.
	 */
	#[Pure] public function isUnarmed(): bool {
		$talent = $this->skill->Talent();
		return $talent === self::$fistfight || $talent === self::$stoning;
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
			self::$stoning       = self::createTalent(Stoning::class);
		}
	}
}
