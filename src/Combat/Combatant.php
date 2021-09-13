<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Combat\Log\Message\AssaultHitMessage;
use function Lemuria\randChance;
use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Factory\Model\Distribution;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat as CombatModel;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Armor;
use Lemuria\Model\Fantasya\Commodity\Ironshield;
use Lemuria\Model\Fantasya\Commodity\Mail;
use Lemuria\Model\Fantasya\Commodity\Woodshield;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Fantasya\Weapon;

/**
 * Combatants are groups of persons from a unit that fight with the same equipment.
 */
class Combatant
{
	use BuilderTrait;

	/**
	 * @var Fighter[]
	 */
	public array $fighters;

	/**
	 * @var array(int=>int)
	 */
	protected static array $ids = [];

	private string $id;

	private int $battleRow;

	private ?Distribution $distribution = null;

	private ?Weapon $weapon = null;

	private ?WeaponSkill $weaponSkill = null;

	private ?Commodity $shield = null;

	private ?Commodity $armor = null;

	private Attack $attack;

	private array $refugees = [];

	public function __construct(private Army $army, private Unit $unit) {
		$this->initId();
		$this->battleRow = Combat::getBattleRow($this->unit);
		$this->attack    = new Attack($this);
	}

	public function Id(): string {
		return $this->id;
	}

	public function Army(): Army {
		return $this->army;
	}

	public function Unit(): Unit {
		return $this->unit;
	}

	public function BattleRow(): int {
		return $this->battleRow;
	}

	public function Distribution(): Distribution {
		return $this->distribution;
	}

	#[Pure] public function Hits(): int {
		return $this->attack->Hits();
	}

	public function Weapon(): Weapon {
		return $this->weapon;
	}

	public function WeaponSkill(): WeaponSkill {
		return $this->weaponSkill;
	}

	public function Shield(): ?Commodity {
		return $this->shield;
	}

	public function Armor(): ?Commodity {
		return $this->armor;
	}

	public function Size(): int {
		return count($this->fighters);
	}

	public function canFlee(): float {
		$chance = $this->attack->getFlightChance();
		return randChance($chance) ? $chance : -$chance;
	}

	public function isFleeing(Fighter $fighter): float|false {
		$calculus     = new Calculus($this->unit);
		$minHitpoints = (int)ceil($calculus->hitpoints() * $this->attack->Flight());
		if ($fighter->health < $minHitpoints) {
			$chance = $this->attack->getFlightChance(true);
			return randChance($chance) ? $chance : -$chance;
		}
		return false;
	}

	public function setBattleRow(int $battleRow): Combatant {
		$this->battleRow = $battleRow;
		$this->initWeaponSkill();
		return $this;
	}

	public function setDistribution(Distribution $distribution): Combatant {
		$calculus           = new Calculus($this->unit);
		$this->distribution = $distribution;
		$this->fighters     = array_fill(0, $distribution->Size(), new Fighter($calculus->hitpoints()));
		$this->initWeaponSkill();
		$this->initShield();
		$this->initArmor();
		return $this;
	}

	public function getId(int $fighter): string {
		return $this->id . '-' . ++$fighter;
	}

	/**
	 * Receive an attack from an assaulting attacker and return the damage done to the defending fighter.
	 */
	public function assault(int $fighter, Combatant $attacker, int $assaulter): int {
		$health = $this->fighters[$fighter]->health;
		$damage = $attacker->attack->perform($assaulter, $this, $fighter);
		if ($damage > 0) {
			$health -= $damage > $health ? $health : $damage;
			Lemuria::Log()->debug('Fighter ' . $attacker->getId($assaulter) . ' deals ' . $damage . ' damage to enemy ' . $this->getId($fighter) . '.');
		}
		if (is_int($damage)) {
			BattleLog::getInstance()->add(new AssaultHitMessage($attacker->getId($assaulter), $this->getId($fighter), $damage));
			$this->fighters[$fighter]->health = $health;
			return $damage;
		}
		return 0;
	}

	public function flee(?int $fighter = null): Combatant {
		if (is_int($fighter)) {
			$this->refugees[] = $this->fighters[$fighter];
			unset($this->fighters[$fighter]);
		} else {
			$this->fighters = $this->refugees;
			$this->refugees = [];
		}
		return $this;
	}

	protected function initWeaponSkill(): void {
		if (is_int($this->battleRow) && $this->distribution) {
			$this->weaponSkill = $this->getWeaponSkill();
		}
	}

	protected function getWeaponSkill(): WeaponSkill {
		$calculus = new Calculus($this->unit);
		$isMelee  = $this->battleRow !== CombatModel::BACK;
		foreach ($calculus->weaponSkill() as $weaponSkill) {
			if ($isMelee && $weaponSkill->isMelee() && $this->hasOneWeaponOf($weaponSkill)) {
				return $weaponSkill;
			}
			if (!$isMelee && $weaponSkill->isDistant() && $this->hasOneWeaponOf($weaponSkill)) {
				return $weaponSkill;
			}
			if ($weaponSkill->isUnarmed() && $this->hasOneWeaponOf($weaponSkill)) {
				return $weaponSkill;
			}
		}
		throw new LemuriaException('Unexpected missing weapon skill.');
	}

	protected function hasOneWeaponOf(WeaponSkill $weaponSkill): bool {
		$talent = $weaponSkill->Skill()->Talent()::class;
		if (!isset(WeaponSkill::WEAPONS[$talent])) {
			throw new LemuriaException('WeaponSkill does not define weapons for ' . $talent . '.');
		}
		foreach (WeaponSkill::WEAPONS[$talent] as $weapon /* @var Weapon $weapon */) {
			if ($this->distribution->offsetExists($weapon)) {
				$this->weapon = self::createWeapon($weapon);
				return true;
			}
		}
		return false;
	}

	protected function initShield(): void {
		if ($this->distribution->offsetExists(Ironshield::class)) {
			$this->shield = self::createCommodity(Ironshield::class);
		}
		if ($this->distribution->offsetExists(Woodshield::class)) {
			$this->shield = self::createCommodity(Woodshield::class);
		}
	}

	protected function initArmor(): void {
		if ($this->distribution->offsetExists(Armor::class)) {
			$this->armor = self::createCommodity(Armor::class);
		}
		if ($this->distribution->offsetExists(Mail::class)) {
			$this->armor = self::createCommodity(Mail::class);
		}
	}

	private function initId(): void {
		$id = $this->unit->Id()->Id();
		if (!isset(self::$ids[$id])) {
			self::$ids[$id] = 0;
		}
		$this->id = $this->unit->Id() . '-' . ++self::$ids[$id];
	}
}
