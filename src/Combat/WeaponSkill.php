<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Archery;
use Lemuria\Model\Fantasya\Talent\Bladefighting;
use Lemuria\Model\Fantasya\Talent\Catapulting;
use Lemuria\Model\Fantasya\Talent\Crossbowing;
use Lemuria\Model\Fantasya\Talent\Fistfight;
use Lemuria\Model\Fantasya\Talent\Spearfighting;
use Lemuria\Model\Fantasya\Talent\Stoning;
use Lemuria\Model\Fantasya\Commodity\Weapon\Battleaxe;
use Lemuria\Model\Fantasya\Commodity\Weapon\Bow;
use Lemuria\Model\Fantasya\Commodity\Weapon\Catapult;
use Lemuria\Model\Fantasya\Commodity\Weapon\Crossbow;
use Lemuria\Model\Fantasya\Commodity\Weapon\Dingbats;
use Lemuria\Model\Fantasya\Commodity\Weapon\Fists;
use Lemuria\Model\Fantasya\Commodity\Weapon\Spear;
use Lemuria\Model\Fantasya\Commodity\Weapon\Sword;
use Lemuria\Model\Fantasya\Commodity\Weapon\Warhammer;

/**
 * Helper class for battle configuration of a unit.
 */
class WeaponSkill
{
	use BuilderTrait;

	public const WEAPONS = [
		Archery::class       => [Bow::class],
		Bladefighting::class => [Battleaxe::class, Warhammer::class, Sword::class],
		Catapulting::class   => [Catapult::class],
		Crossbowing::class   => [Crossbow::class],
		Fistfight::class     => [Fists::class],
		Spearfighting::class => [Spear::class],
		Stoning::class       => [Dingbats::class]
	];

	private static ?Talent $archery = null;

	private static Talent $bladefighting;

	private static Talent $catapulting;

	private static Talent $crossbowing;

	private static Talent $fistfight;

	private static Talent $spearfighting;

	private static Talent $stoning;

	public static function isSkill(Talent $talent): bool {
		self::initTalents();
		return match ($talent) {
			self::$bladefighting, self::$spearfighting,	self::$archery, self::$crossbowing, self::$catapulting => true,
			default => false
		};
	}

	public function __construct(private Ability $skill) {
		self::initTalents();
	}

	#[Pure] public function Skill(): Ability {
		return $this->skill;
	}

	/**
	 * Check if weapon skill is suitable for short distance combat.
	 *
	 * @noinspection PhpPureFunctionMayProduceSideEffectsInspection
	 */
	#[Pure] public function isMelee(): bool {
		$talent = $this->skill->Talent();
		return $talent === self::$bladefighting || $talent === self::$spearfighting;
	}

	/**
	 * Check if weapon skill is suitable for long distance combat.
	 *
	 * @noinspection PhpPureFunctionMayProduceSideEffectsInspection
	 */
	#[Pure] public function isDistant(): bool {
		$talent = $this->skill->Talent();
		return $talent === self::$archery || $talent === self::$crossbowing || $talent === self::$catapulting;
	}

	/**
	 * Check if weapon skill is suitable for guards or tax collectors.
	 *
	 * @noinspection PhpPureFunctionMayProduceSideEffectsInspection
	 */
	#[Pure] public function isGuard(): bool {
		return !$this->isUnarmed() && !$this->isSiege();
	}

	/**
	 * Check if weapon skill is suitable for siege.
	 *
	 * @noinspection PhpPureFunctionMayProduceSideEffectsInspection
	 */
	#[Pure] public function isSiege(): bool {
		return $this->skill->Talent() === self::$catapulting;
	}

	/**
	 * Check if weapon skill is unarmed.
	 *
	 * @noinspection PhpPureFunctionMayProduceSideEffectsInspection
	 */
	#[Pure] public function isUnarmed(): bool {
		$talent = $this->skill->Talent();
		return $talent === self::$fistfight || $talent === self::$stoning;
	}

	/**
	 * Init static talents.
	 */
	protected static function initTalents() {
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
