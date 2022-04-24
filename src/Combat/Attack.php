<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use function Lemuria\randChance;
use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Combat\Log\Message\AssaultBlockMessage;
use Lemuria\Engine\Fantasya\Command\Apply\BerserkBlood as BerserkBloodEffect;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Potion\BerserkBlood;
use Lemuria\Model\Fantasya\Commodity\Protection\Armor;
use Lemuria\Model\Fantasya\Commodity\Protection\Ironshield;
use Lemuria\Model\Fantasya\Commodity\Protection\LeatherArmor;
use Lemuria\Model\Fantasya\Commodity\Protection\Mail;
use Lemuria\Model\Fantasya\Commodity\Protection\Woodshield;
use Lemuria\Model\Fantasya\Commodity\Weapon\Bow;
use Lemuria\Model\Fantasya\Commodity\Weapon\Spear;
use Lemuria\Model\Fantasya\Commodity\Weapon\WarElephant;
use Lemuria\Model\Fantasya\Protection;
use Lemuria\Model\Fantasya\Race\Monster;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Riding;
use Lemuria\Model\Fantasya\Weapon;

class Attack
{
	protected const DAMAGE_BONUS = [
		Bow::class => 0.5
	];

	protected const BLOCK_BONUS = [
		Ironshield::class => 2,
		Woodshield::class => 1
	];

	protected const ATTACK_MALUS = [
		Armor::class        => 2,
		LeatherArmor::class => 0,
		Mail::class         => 1
	];

	protected const ATTACK_FAILURE = [
		WarElephant::class => [Spear::class]
	];

	protected const FLIGHT = [1.0, 0.9, 0.9, 0.9, 0.2, 0.2, 0.0];

	protected int $round = 0;

	private float $flight;

	#[Pure] public function __construct(private Combatant $combatant) {
		$this->flight = self::FLIGHT[$combatant->Unit()->BattleRow()->value];
	}

	public function Flight(): float {
		return $this->flight;
	}

	public function getFlightChance(bool $isFighting = false): float {
		$unit     = $this->combatant->Unit();
		$calculus = new Calculus($unit);
		$chance   = $unit->Race()->FlightChance();
		if ($isFighting) {
			$chance += $this->combatant->WeaponSkill()->Skill()->Level() * 0.05;
			if ($this->combatant->Distribution()->offsetExists(WarElephant::class)) {
				if ($calculus->knowledge(Riding::class)->Level() > 1) {
					$chance += 0.5;
				}
			} elseif ($this->combatant->Distribution()->offsetExists(Horse::class)) {
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
		$attacker    = $this->combatant->fighter($fA);
		$attacks     = 1;
		$weapon      = $this->combatant->Weapon();
		$weaponSkill = $this->combatant->WeaponSkill();
		$interval    = $weapon->Interval();
		if ($attacker->quickening > 0) {
			$interval = min(1, (int)ceil($interval / 2)); // Reduce interval for quickened fighters by half.
			if (!$weaponSkill->isSiege()) {
				$attacks = 2; // Allow two attacks for quickened fighters that have a normal weapon.
			}
			$keepChance = $attacker->quickening / ($this->round + 1);
			if (!randChance($keepChance)) {
				$attacker->quickening = 0; // Stop Quickening after minimum duration with increasing chance.
			}
		}

		if ($this->round++ % $interval > 0) {
			// Lemuria::Log()->debug('Fighter ' . $this->combatant->getId($fA, true) . ' is not ready yet.');
			return null;
		}
		if ($fA < $this->combatant->distracted) {
			// Lemuria::Log()->debug('Fighter ' . $this->combatant->getId($fA, true) . ' is distracted.');
			return null;
		}

		$skill     = $weaponSkill->Skill()->Level();
		$defWeapon = $defender->Weapon()::class;
		if (isset(self::ATTACK_FAILURE[$defWeapon])) {
			$attWeapon = $weapon::class;
			if (!isset(self::ATTACK_FAILURE[$defWeapon][$attWeapon])) {
				$skill = 0;
				// Lemuria::Log()->debug('Fighter ' . $this->combatant->getId($fA, true) . ' has no chance against ' . $defender->getId($fD, true) . '.');
			}
		}

		$armor    = $this->combatant->Armor();
		$hasBonus = $attacker->potion instanceof BerserkBlood;
		$shield   = $defender->Shield();
		$block    = $fD < $defender->distracted ? 0 : $defender->WeaponSkill()->Skill()->Level();

		$damage = null;
		for ($i = 0; $i < $attacks; $i++) {
			if ($this->isSuccessful($skill, $block, $armor, $shield, $hasBonus)) {
				// Lemuria::Log()->debug('Fighter ' . $this->combatant->getId($fA, true) . ' hits enemy ' . $defender->getId($fD, true) . '.');
				$race = $defender->Unit()->Race();
				/** @noinspection PhpPossiblePolymorphicInvocationInspection */
				$block   = $race instanceof Monster ? $race->Block() : 0;
				$armor   = $defender->Armor();
				$damage += $this->calculateDamage($weapon, $skill, $block, $armor, $shield);
			}
		}
		if ($damage === null) {
			// Lemuria::Log()->debug('Enemy ' . $defender->getId($fD, true) . ' blocks attack from ' . $this->combatant->getId($fA, true) . '.');
			BattleLog::getInstance()->add(new AssaultBlockMessage($this->combatant->getId($fA, true), $defender->getId($fD)));
		}
		return $damage;
	}

	protected function isSuccessful(int $skill, int $block, ?Protection $armor, ?Protection $shield, bool $hasAttackBonus): bool {
		$malus = 0;
		if ($hasAttackBonus) {
			$malus = -BerserkBloodEffect::BONUS;
		} elseif ($armor) {
			$malus = self::ATTACK_MALUS[$armor::class];
		}
		$bonus = $shield ? self::BLOCK_BONUS[$shield::class] : 0;

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

	protected function calculateDamage(Weapon $weapon, int $skill, int $block, ?Protection $armor, ?Protection $shield): int {
		$damage  = $this->combatant->Weapon()->Damage();
		$bonus   = self::DAMAGE_BONUS[$weapon::class] ?? 0.0;
		$b       = $bonus > 0.0 ? (int)floor($bonus * $skill) : 0;
		$attack  = $damage->Count() * rand(1, $damage->Dice() + $b) + $damage->Addition();
		$block  += $shield?->Block() + $armor?->Block();
		// Lemuria::Log()->debug('Damage calculation: ' . getClass($weapon) . ':' . $damage . ' bonus:' . $b . ' damage:' . $attack . '-' . $block);
		return max(0, $attack - $block);
	}
}
