<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use function Lemuria\randChance;
use function Lemuria\randInt;
use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Combat\Log\Message\AssaultBlockMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\AssaultPetrifiedMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\GazeOfTheBasiliskMessage;
use Lemuria\Engine\Fantasya\Combat\Spell\StoneSkin;
use Lemuria\Engine\Fantasya\Command\Apply\BerserkBlood as BerserkBloodEffect;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\BattleSpell;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Potion\BerserkBlood;
use Lemuria\Model\Fantasya\Commodity\Protection\Armor;
use Lemuria\Model\Fantasya\Commodity\Protection\Ironshield;
use Lemuria\Model\Fantasya\Commodity\Protection\LeatherArmor;
use Lemuria\Model\Fantasya\Commodity\Protection\Mail;
use Lemuria\Model\Fantasya\Commodity\Protection\Repairable\DentedArmor;
use Lemuria\Model\Fantasya\Commodity\Protection\Repairable\DentedIronshield;
use Lemuria\Model\Fantasya\Commodity\Protection\Repairable\RustyMail;
use Lemuria\Model\Fantasya\Commodity\Protection\Repairable\SplitWoodshield;
use Lemuria\Model\Fantasya\Commodity\Protection\Repairable\TatteredLeatherArmor;
use Lemuria\Model\Fantasya\Commodity\Protection\Woodshield;
use Lemuria\Model\Fantasya\Commodity\Weapon\Bow;
use Lemuria\Model\Fantasya\Commodity\Weapon\Catapult;
use Lemuria\Model\Fantasya\Commodity\Weapon\Claymore;
use Lemuria\Model\Fantasya\Commodity\Weapon\Crossbow;
use Lemuria\Model\Fantasya\Commodity\Weapon\Halberd;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\BentHalberd;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\RustyClaymore;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\SkewedCatapult;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\UngirtBow;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\UngirtCrossbow;
use Lemuria\Model\Fantasya\Commodity\Weapon\Spear;
use Lemuria\Model\Fantasya\Commodity\Weapon\WarElephant;
use Lemuria\Model\Fantasya\Commodity\Weapon\Warhammer;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Monster;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Protection;
use Lemuria\Model\Fantasya\Spell\GustOfWind;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Riding;
use Lemuria\Model\Fantasya\Weapon;

class Attack
{
	use BuilderTrait;

	/**
	 * @type array<string, true>
	 */
	public final const array TWO_HANDED = [
		Claymore::class    => true, Halberd::class       => true,
		BentHalberd::class => true, RustyClaymore::class => true
	];

	/**
	 * @type array<string, float>
	 */
	protected const array DAMAGE_BONUS = [
		Bow::class => 0.5
	];

	/**
	 * @type array<string, int>
	 */
	protected const array BLOCK_BONUS = [
		Ironshield::class => 2,
		Woodshield::class => 1,

		DentedIronshield::class => 1,
		SplitWoodshield::class  => 0
	];

	protected const array ATTACK_MALUS = [
		Armor::class        => 2,
		LeatherArmor::class => 0,
		Mail::class         => 1,

		DentedArmor::class          => 2,
		TatteredLeatherArmor::class => 0,
		RustyMail::class            => 1
	];

	/**
	 * @type array<string, array<string>>
	 */
	protected const array ATTACK_FAILURE = [
		WarElephant::class => [Halberd::class, Spear::class]
	];

	/**
	 * @type array<string>
	 */
	protected const array BASILISK_WEAPON = [Warhammer::class, Catapult::class];

	/**
	 * @type array<string, float>
	 */
	protected const array WIND_EFFECT = [
		Bow::class      => 1.0,
		Catapult::class => 0.2,
		Crossbow::class => 0.5,

		UngirtBow::class      => 2.0,
		SkewedCatapult::class => 0.4,
		UngirtCrossbow::class => 1.0
	];

	/**
	 * @type array<float>
	 */
	protected const array FLIGHT = [1.0, 0.9, 0.9, 0.9, 0.2, 0.2, 0.0];

	protected const float BLOCK_EFFECT = 0.5;

	protected const float INFECTION = 0.3;

	private float $flight;

	private static ?BattleSpell $gustOfWind = null;

	public function __construct(private Combatant $combatant) {
		$this->flight = self::FLIGHT[$combatant->Unit()->BattleRow()->value];
		if (!self::$gustOfWind) {
			/** @var BattleSpell $spell */
			$spell            = self::createSpell(GustOfWind::class);
			self::$gustOfWind = $spell;
		}
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
		$attacker = $this->combatant->fighter($fA);
		if ($attacker->hasFeature(Feature::GazeOfTheBasilisk)) {
			BattleLog::getInstance()->add(new GazeOfTheBasiliskMessage($this->combatant->getId($fA, true)));
			//Lemuria::Log()->debug('Fighter ' . $this->combatant->getId($fA) . ' is petrified by Gaze of the Basilisk.');
			return null;
		}

		$attacks     = 1;
		$weapon      = $this->combatant->Weapon();
		$weaponSkill = $this->combatant->WeaponSkill();
		$interval    = $weapon->Interval();
		$quickening  = $attacker->useQuickening();
		if ($quickening > 0) {
			$interval = min(1, (int)ceil($interval / 2)); // Reduce interval for quickened fighters by half.
			if (!$weaponSkill->isSiege()) {
				$attacks = 2; // Allow two attacks for quickened fighters that have a normal weapon.
			}
		}

		if ($attacker->round % $interval > 0) {
			// Lemuria::Log()->debug('Fighter ' . $this->combatant->getId($fA, true) . ' is not ready yet.');
			return null;
		}
		if ($attacker->hasFeature(Feature::ShockWave)) {
			// Lemuria::Log()->debug('Fighter ' . $this->combatant->getId($fA, true) . ' is distracted.');
			return null;
		}

		$skill     = $weaponSkill->Skill()->Level();
		$attWeapon = $weapon::class;
		$defWeapon = $defender->Weapon()::class;
		if (isset(self::ATTACK_FAILURE[$defWeapon])) {
			if (!isset(self::ATTACK_FAILURE[$defWeapon][$attWeapon])) {
				$skill = 0;
				// Lemuria::Log()->debug('Fighter ' . $this->combatant->getId($fA, true) . ' has no chance against ' . $defender->getId($fD, true) . '.');
			}
		}

		// Reduce distant weapon skill for Gust Of Wind effect.
		if (isset(self::WIND_EFFECT[$attWeapon])) {
			$gustOfWind = $this->combatant->Army()->Combat()->getEffect(self::$gustOfWind, $this->combatant);
			if ($gustOfWind) {
				$effect = self::WIND_EFFECT[$attWeapon];
				$level  = $gustOfWind->Count();
				$factor = 1.0 / ($level / self::$gustOfWind->Difficulty() + 1);
				$malus  = (int)floor($effect * $skill * $factor);
				$skill  = max(0, $skill - $malus);
				// Lemuria::Log()->debug('Fighter ' . $this->combatant->getId($fA, true) . ' has Gust Of Wind malus of ' . $malus . '.');
			}
		}

		$armor      = $this->combatant->Armor();
		$defFighter = $defender->fighter($fD);
		$shield     = $defender->Shield();
		$block      = $defender->WeaponSkill()->Skill()->Level();
		if ($defFighter->hasFeature(Feature::ShockWave)) {
			$block = 0;
		}
		if ($defFighter->hasFeature(Feature::GazeOfTheBasilisk)) {
			$block  = 0;
			$shield = null;
		}
		$hasBonus = null;
		if ($attacker->potion instanceof BerserkBlood) {
			$hasBonus = true;
		} elseif ($attacker->hasFeature(Feature::StoneSkin)) {
			$hasBonus = false;
		}

		$attInfectious = $attacker->hasFeature(Feature::ZombieInfection);
		$defInfectious = $defFighter->hasFeature(Feature::ZombieInfection);
		$isInfectious  = $attInfectious && !$defInfectious && $defender->Unit()->Party()->Type() !== Type::Monster;

		$damage = null;
		for ($i = 0; $i < $attacks; $i++) {
			if ($this->isSuccessful($skill, $block, $armor, $shield, $hasBonus)) {
				// Lemuria::Log()->debug('Fighter ' . $this->combatant->getId($fA, true) . ' hits enemy ' . $defender->getId($fD, true) . '.');
				if ($defFighter->hasFeature(Feature::GazeOfTheBasilisk)) {
					if (!in_array($attWeapon, self::BASILISK_WEAPON)) {
						//Lemuria::Log()->debug('Fighter ' . $this->combatant->getId($fA, true) . ' cannot hurt ' . $defender->getId($fD, true) . ' who is protected by Gaze of the Basilisk.');
						BattleLog::getInstance()->add(new AssaultPetrifiedMessage($this->combatant->getId($fA, true), $defender->getId($fD)));
						return null;
					}
				}
				$race    = $defender->Unit()->Race();
				$block   = $race instanceof Monster ? $race->Block() : 0;
				$block  += $defFighter->hasFeature(Feature::StoneSkin) ? StoneSkin::BLOCK : 0;
				$armor   = $defender->Armor();
				$hit     = $this->calculateDamage($weapon, $skill, $block, $armor, $shield);
				$damage += $hit;

				if ($isInfectious && $hit > 0) {
					$chance = $hit / $weapon->Damage()->Maximum() * self::INFECTION;
					if (randChance($chance)) {
						$defFighter->setFeature(Feature::ZombieInfection);
						$isInfectious = false;
						Lemuria::Log()->debug('Enemy ' . $defender->getId($fD, true) . ' is infected by ' . $this->combatant->getId($fA, true) . '.');
					}
				}
			}
		}
		if ($damage === null) {
			// Lemuria::Log()->debug('Enemy ' . $defender->getId($fD, true) . ' blocks attack from ' . $this->combatant->getId($fA, true) . '.');
			BattleLog::getInstance()->add(new AssaultBlockMessage($this->combatant->getId($fA, true), $defender->getId($fD)));
		}
		return $damage;
	}

	protected function isSuccessful(int $skill, int $block, ?Protection $armor, ?Protection $shield, bool|null $hasAttackBonus): bool {
		if ($hasAttackBonus) {
			$malus = -BerserkBloodEffect::BONUS;
		} else {
			$malus = 0;
			if ($hasAttackBonus === false) {
				$malus += StoneSkin::ATTACK_MALUS;
			}
			if ($armor) {
				$malus += self::ATTACK_MALUS[$armor::class];
			}
		}
		$bonus = $shield ? self::BLOCK_BONUS[$shield::class] : 0;

		$attSkill = $skill - $malus;
		$defSkill = $block + $bonus;
		if ($attSkill > 0) {
			if ($defSkill > 0) {
				$sum = $attSkill + $defSkill - 1;
				$hit = randInt(0, $sum);
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
		$attack  = $damage->Count() * randInt(1, $damage->Dice() + $b) + $damage->Addition();
		$block  += $this->getBlockValue($shield) + $this->getBlockValue($armor);
		// Lemuria::Log()->debug('Damage calculation: ' . getClass($weapon) . ':' . $damage . ' bonus:' . $b . ' damage:' . $attack . '-' . $block);
		return max(0, $attack - $block);
	}

	private function getBlockValue(?Protection $protection): int {
		if ($protection) {
			$max = $protection->Block();
			$min = (int)ceil(self::BLOCK_EFFECT * $max);
			return randInt($min, $max);
		}
		return 0;
	}
}
