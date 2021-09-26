<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Combat\Log\Message\AssaultBlockMessage;
use Lemuria\Engine\Fantasya\Command\Apply\BerserkBlood as BerserkBloodEffect;
use Lemuria\Model\Fantasya\Combat;
use Lemuria\Model\Fantasya\Commodity\Armor;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Ironshield;
use Lemuria\Model\Fantasya\Commodity\Mail;
use Lemuria\Model\Fantasya\Commodity\Potion\BerserkBlood;
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
use Lemuria\Model\Fantasya\Race\Aquan;
use Lemuria\Model\Fantasya\Race\Dwarf;
use Lemuria\Model\Fantasya\Race\Elf;
use Lemuria\Model\Fantasya\Race\Halfling;
use Lemuria\Model\Fantasya\Race\Human;
use Lemuria\Model\Fantasya\Race\Orc;
use Lemuria\Model\Fantasya\Race\Troll;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Riding;

class Attack
{
	protected const HITS = [
		Catapult::class => 3
	];

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
		Catapult::class  => [3, 10, 5],
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

	protected const FLIGHT = [
		Combat::REFUGEE    => 1.0,
		Combat::BYSTANDER  => 0.9, Combat::DEFENSIVE => 0.9,
		Combat::BACK       => 0.2, Combat::FRONT     => 0.2,
		Combat::AGGRESSIVE => 0.0
	];

	protected const FLIGHT_CHANCE = [
		Aquan::class    => 0.25,
		Dwarf::class    => 0.25,
		Elf::class      => 0.25,
		Human::class    => 0.25,
		Halfling::class => 0.5,
		Orc::class      => 0.25,
		Troll::class    => 0.25
	];

	protected int $round = 0;

	private float $flight;

	#[Pure] public function __construct(private Combatant $combatant) {
		$this->flight = self::FLIGHT[$combatant->Unit()->BattleRow()];
	}

	#[Pure] public function Hits(): int {
		$weapon = $this->combatant->Weapon()::class;
		return self::HITS[$weapon] ?? 1;
	}

	public function Flight(): float {
		return $this->flight;
	}

	public function getFlightChance(bool $isFighting = false): float {
		$unit     = $this->combatant->Unit();
		$calculus = new Calculus($unit);
		$chance   = self::FLIGHT_CHANCE[$unit->Race()::class];
		if ($isFighting) {
			$chance += $this->combatant->WeaponSkill()->Skill()->Level() * 0.05;
			if ($this->combatant->Distribution()->offsetExists(Horse::class)) {
				if ($calculus->knowledge(Riding::class)->Level() > 0) {
					$chance += 0.25;
				}
			}
		} else {
			$chance += $calculus->knowledge(Camouflage::class)->Level() * 0.05;
		}
		return min(1.0, $chance);
	}

	public function perform(int $fA, Combatant $defender, int $fD): ?int {
		$weapon   = $this->combatant->Weapon()::class;
		$interval = self::INTERVAL[$weapon] ?? 1;
		if ($this->round++ % $interval > 0) {
			// Lemuria::Log()->debug('Fighter ' . $this->combatant->getId($fA) . ' is not ready yet.');
			return null;
		}
		if ($fA < $this->combatant->distracted) {
			// Lemuria::Log()->debug('Fighter ' . $this->combatant->getId($fA) . ' is distracted.');
			return null;
		}

		$skill    = $this->combatant->WeaponSkill()->Skill()->Level();
		$armor    = $this->combatant->Armor();
		$aClass   = $armor ? $armor::class : null;
		$hasBonus = $this->combatant->fighters[$fA]->potion instanceof BerserkBlood;

		if ($fD < $defender->distracted) {
			$block  = 0;
			$sClass = null;
		} else {
			$block  = $defender->WeaponSkill()->Skill()->Level();
			$shield = $defender->Shield();
			$sClass = $shield ? $shield::class : null;
		}

		if ($this->isSuccessful($skill, $block, $aClass, $sClass, $hasBonus)) {
			// Lemuria::Log()->debug('Fighter ' . $this->combatant->getId($fA) . ' hits enemy ' . $defender->getId($fD) . '.');
			$armor  = $defender->Armor();
			$aClass = $armor ? $armor::class : null;
			return $this->calculateDamage($weapon, $skill, $aClass, $sClass);
		} else {
			// Lemuria::Log()->debug('Enemy ' . $defender->getId($fD) . ' blocks attack from ' . $this->combatant->getId($fA) . '.');
			BattleLog::getInstance()->add(new AssaultBlockMessage($this->combatant->getId($fA), $defender->getId($fD)));
			return null;
		}
	}

	protected function isSuccessful(int $skill, int $block, ?string $armor, ?string $shield, bool $hasAttackBonus): bool {
		$malus = 0;
		if ($hasAttackBonus) {
			$malus = -BerserkBloodEffect::BONUS;
		} elseif ($armor) {
			$malus = self::ATTACK_MALUS[$armor];
		}
		$bonus = 0;
		if ($shield) {
			$bonus = self::BLOCK_BONUS[$shield];
		}
		$attSkill = $skill - $malus;
		$defSkill = $block + $bonus;
		if ($attSkill > 0) {
			if ($defSkill > 0) {
				$sum = $attSkill + $defSkill - 1;
				$hit = rand(0, $sum);
				// Lemuria::Log()->debug('Attack/Block calculation: A:' . $skill . '-' . $malus . ' B:' . $block . '+' . $bonus . ' hit:' . $hit . '/' . $sum);
				return $hit < $attSkill;
			}
			// Lemuria::Log()->debug('Attack is successful, defender has no skill.');
			return true;
		}
		// Lemuria::Log()->debug('Attack is not successful, attacker has no skill.');
		return false;
	}

	protected function calculateDamage(string $weapon, int $skill, ?string $armor, ?string $shield): int {
		$damage = self::DAMAGE[$weapon];
		$n      = $damage[0];
		$d      = $damage[1];
		$p      = $damage[2];
		$dice   = $d;
		$bonus  = self::DAMAGE_BONUS[$weapon] ?? 0.0;
		$b      = $bonus > 0.0 ? (int)floor($bonus * $skill) : 0;
		$attack = $n * rand(1, $dice + $b) + $p;
		$block  = ($shield ? self::BLOCK[$shield] : 0) + ($armor ? self::BLOCK[$armor] : 0);
		// Lemuria::Log()->debug('Damage calculation: ' . getClass($weapon) . ':' . $n . 'd' . $d . '+' . $p . ' bonus:' . $b . ' damage:' . $attack . '-' . $block);
		return max(0, $attack - $block);
	}
}
