<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Armor;
use Lemuria\Model\Fantasya\Commodity\Ironshield;
use Lemuria\Model\Fantasya\Commodity\Mail;
use Lemuria\Model\Fantasya\Commodity\Weapon\Battleaxe;
use Lemuria\Model\Fantasya\Commodity\Weapon\Bow;
use Lemuria\Model\Fantasya\Commodity\Weapon\Catapult;
use Lemuria\Model\Fantasya\Commodity\Weapon\Crossbow;
use Lemuria\Model\Fantasya\Commodity\Weapon\Dingbats;
use Lemuria\Model\Fantasya\Commodity\Weapon\Fists;
use Lemuria\Model\Fantasya\Commodity\Weapon\Spear;
use Lemuria\Model\Fantasya\Commodity\Weapon\Sword;
use Lemuria\Model\Fantasya\Commodity\Weapon\Warhammer;
use Lemuria\Model\Fantasya\Commodity\Woodshield;

class Attack
{
	protected const INTERVAL = [
		Catapult::class => 5,
		Crossbow::class => 2
	];

	/**
	 * Weapon damage definition like "1 W6 + 3".
	 */
	protected const DAMAGE = [
		Battleaxe::class => [1, 8, 8],
		Bow::class       => [1, 4, 4],
		Catapult::class  => [3, 20, 0],
		Crossbow::class  => [2, 4, 6],
		Dingbats::class  => [1, 5, 0],
		Fists::class     => [1, 5, 0],
		Spear::class     => [1, 7, 3],
		Sword::class     => [1, 8, 4],
		Warhammer::class => [1, 8, 8],
	];

	protected const DAMAGE_BONUS = [
		Bow::class => 0.5
	];

	protected const BLOCK = [
		Armor::class      => 7,
		Ironshield::class => 6,
		Mail::class       => 5,
		Woodshield::class => 4
	];

	protected const BLOCK_BONUS = [
		Ironshield::class => 2,
		Woodshield::class => 1
	];

	protected const ATTACK_MALUS = [
		Armor::class => 2,
		Mail::class  => 1
	];

	protected int $round = 0;

	public function __construct(private Combatant $combatant) {
	}

	public function perform(int $cA, int $fA, Combatant $defender, int $cD, int $fD): int {
		$weapon   = $this->combatant->Weapon()::class;
		$interval = self::INTERVAL[$weapon] ?? 1;
		if ($this->round++ % $interval > 0) {
			Lemuria::Log()->debug('Fighter ' . $cA . '/' . $fA . ' is not ready yet.');
			return 0;
		}

		$damage = 0;
		$skill  = $this->combatant->WeaponSkill()->Skill()->Level();
		$block  = $defender->WeaponSkill()->Skill()->Level();
		$armor  = $this->combatant->Armor();
		$aClass = $armor ? $armor::class : null;
		$shield = $defender->Shield();
		$sClass = $shield ? $shield::class : null;

		if ($this->isSuccessful($skill, $block, $aClass, $sClass)) {
			Lemuria::Log()->debug('Fighter ' . $cA . '/' . $fA . ' hits enemy ' . $cD . '/' . $fD . '.');
			$armor  = $defender->Armor();
			$aClass = $armor ? $armor::class : null;
			$damage = $this->calculateDamage($weapon, $skill, $aClass, $sClass);
		} else {
			Lemuria::Log()->debug('Enemy ' . $cD . '/' . $fD . ' blocks attack from ' . $cA . '/' . $fA . '.');
		}
		return $damage;
	}

	protected function isSuccessful(int $skill, int $block, ?string $armor, ?string $shield): bool {
		if ($armor) {
			$skill -= self::ATTACK_MALUS[$armor];
		}
		if ($shield) {
			$block += self::BLOCK_BONUS[$shield];
		}
		$sum = $skill + $block - 1;
		if ($sum > 0) {
			$hit = rand(0, $sum);
			return $hit < $skill;
		}
		return false;
	}

	protected function calculateDamage(string $weapon, int $skill, ?string $armor, ?string $shield): int {
		$damage = self::DAMAGE[$weapon];
		$dice   = $damage[1];
		$bonus  = self::DAMAGE_BONUS[$weapon] ?? 0.0;
		if ($bonus > 0.0) {
			$dice += (int)floor($bonus * $skill);
		}
		$attack = $damage[0] * rand(1, $dice) + $damage[2];
		$block  = ($shield ? self::BLOCK[$shield] : 0) + ($armor ? self::BLOCK[$armor] : 0);
		return max(0, $attack - $block);
	}
}
