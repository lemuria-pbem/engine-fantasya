<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Commodity\Weapon\Claymore;
use Lemuria\Model\Fantasya\Commodity\Weapon\Halberd;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\BentHalberd;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\FounderingWarElephant;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\LooseWarhammer;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\RustyBattleaxe;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\RustyClaymore;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\RustySword;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\SkewedCatapult;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\StumpSpear;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\UngirtBow;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\UngirtCrossbow;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Archery;
use Lemuria\Model\Fantasya\Talent\Bladefighting;
use Lemuria\Model\Fantasya\Talent\Catapulting;
use Lemuria\Model\Fantasya\Talent\Crossbowing;
use Lemuria\Model\Fantasya\Talent\Fistfight;
use Lemuria\Model\Fantasya\Talent\Riding;
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
use Lemuria\Model\Fantasya\Commodity\Weapon\WarElephant;
use Lemuria\Model\Fantasya\Commodity\Weapon\Warhammer;

/**
 * Helper class for battle configuration of a unit.
 */
class WeaponSkill
{
	use BuilderTrait;

	public const WEAPONS = [
		Archery::class       => [Bow::class, UngirtBow::class],
		Bladefighting::class => [
			Battleaxe::class, Claymore::class, Warhammer::class, Sword::class,
			RustyBattleaxe::class, RustyClaymore::class, LooseWarhammer::class, RustySword::class
		],
		Catapulting::class   => [Catapult::class, SkewedCatapult::class],
		Crossbowing::class   => [Crossbow::class, UngirtCrossbow::class],
		Fistfight::class     => [Fists::class],
		Riding::class        => [WarElephant::class, FounderingWarElephant::class],
		Spearfighting::class => [
			Halberd::class, Spear::class,
			BentHalberd::class, StumpSpear::class
		],
		Stoning::class       => [Dingbats::class]
	];

	private static ?Talent $archery = null;

	private static Talent $bladefighting;

	private static Talent $catapulting;

	private static Talent $crossbowing;

	private static Talent $fistfight;

	private static Talent $riding;

	private static Talent $spearfighting;

	private static Talent $stoning;

	public static function isSkill(Ability $ability): bool {
		self::initTalents();
		return match ($ability->Talent()) {
			self::$bladefighting, self::$spearfighting,	self::$archery, self::$crossbowing, self::$catapulting => $ability->Level() >= 1,
			self::$riding => $ability->Level() >= 2, // War elephant
			default => false
		};
	}

	public function __construct(private Ability $skill) {
		self::initTalents();
	}

	public function Skill(): Ability {
		return $this->skill;
	}

	/**
	 * Check if weapon skill is suitable for short distance combat.
	 */
	public function isMelee(): bool {
		$talent = $this->skill->Talent();
		return $talent === self::$bladefighting || $talent === self::$spearfighting || $talent === self::$riding;
	}

	/**
	 * Check if weapon skill is suitable for long distance combat.
	 */
	public function isDistant(): bool {
		$talent = $this->skill->Talent();
		return $talent === self::$archery || $talent === self::$crossbowing || $talent === self::$catapulting;
	}

	/**
	 * Check if weapon skill is suitable for guards or tax collectors.
	 */
	public function isGuard(): bool {
		return !$this->isUnarmed() && !$this->isSiege() && $this->skill->Talent() !== self::$riding;
	}

	/**
	 * Check if weapon skill is suitable for siege.
	 */
	public function isSiege(): bool {
		return $this->skill->Talent() === self::$catapulting;
	}

	/**
	 * Check if weapon skill is unarmed.
	 */
	public function isUnarmed(): bool {
		$talent = $this->skill->Talent();
		return $talent === self::$fistfight || $talent === self::$stoning;
	}

	/**
	 * Init static talents.
	 */
	protected static function initTalents(): void {
		if (!self::$archery) {
			self::$archery       = self::createTalent(Archery::class);
			self::$bladefighting = self::createTalent(Bladefighting::class);
			self::$catapulting   = self::createTalent(Catapulting::class);
			self::$crossbowing   = self::createTalent(Crossbowing::class);
			self::$fistfight     = self::createTalent(Fistfight::class);
			self::$riding        = self::createTalent(Riding::class);
			self::$spearfighting = self::createTalent(Spearfighting::class);
			self::$stoning       = self::createTalent(Stoning::class);
		}
	}
}
