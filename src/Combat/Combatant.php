<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use function Lemuria\randChance;
use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Combat\Log\Message\AssaultHitMessage;
use Lemuria\Engine\Fantasya\Factory\Model\Distribution;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Armature;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Weapon\Dingbats;
use Lemuria\Model\Fantasya\Commodity\Weapon\Fists;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Monster;
use Lemuria\Model\Fantasya\Commodity\Monster\Zombie;
use Lemuria\Model\Fantasya\Commodity\Weapon\NativeDistant;
use Lemuria\Model\Fantasya\Commodity\Weapon\NativeMelee;
use Lemuria\Model\Fantasya\Protection;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Shield;
use Lemuria\Model\Fantasya\Talent\Fistfight;
use Lemuria\Model\Fantasya\Talent\Stoning;
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
	 * @var Fighter[]
	 */
	public array $refugees = [];

	public bool $hasCast = false;

	/**
	 * @var array(int=>int)
	 */
	protected static array $ids = [];

	private string $id;

	private BattleRow $battleRow;

	private ?Distribution $distribution = null;

	private ?Weapon $weapon = null;

	private ?WeaponSkill $weaponSkill = null;

	private ?Protection $shield = null;

	private ?Protection $armor = null;

	private Attack $attack;

	/**
	 * @var int[]
	 */
	private array $fighterIndex;

	public function __construct(private Army $army, private Unit $unit) {
		$this->initId();
		$this->battleRow = $this->unit->BattleRow();
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

	public function BattleRow(): BattleRow {
		return $this->battleRow;
	}

	public function Distribution(): Distribution {
		return $this->distribution;
	}

	public function Weapon(): Weapon {
		return $this->weapon;
	}

	public function WeaponSkill(): WeaponSkill {
		return $this->weaponSkill;
	}

	public function Shield(): ?Protection {
		return $this->shield;
	}

	public function Armor(): ?Protection {
		return $this->armor;
	}

	public function Size(): int {
		return count($this->fighters);
	}

	public function fighter(int $index): Fighter {
		return $this->fighters[$this->fighterIndex[$index]];
	}

	public function canFlee(): float {
		$chance = $this->attack->getFlightChance();
		return randChance($chance) ? $chance : -$chance;
	}

	public function isFleeing(Fighter $fighter): float|false {
		if ($fighter->HasBeenHealed()) {
			return 1.0;
		}

		$calculus     = new Calculus($this->unit);
		$minHitpoints = (int)ceil($calculus->hitpoints() * $this->attack->Flight());
		if ($fighter->health < $minHitpoints) {
			$chance = $this->attack->getFlightChance(true);
			return randChance($chance) ? $chance : -$chance;
		}
		return false;
	}

	public function setBattleRow(BattleRow $battleRow): Combatant {
		$this->battleRow = $battleRow;
		$this->initWeaponSkill();
		return $this;
	}

	public function setDistribution(Distribution $distribution): Combatant {
		$n                  = $distribution->Size();
		$calculus           = new Calculus($this->unit);
		$this->distribution = $distribution;
		$this->fighters     = array_fill(0, $n, null);
		for ($i = 0; $i < $n; $i++) {
			$this->fighters[$i] = new Fighter($calculus->hitpoints());
			$this->fighterIndex = array_keys($this->fighters);
		}
		$this->initWeaponSkill();
		$this->initShieldAndArmor();
		$this->initFeatures();
		return $this;
	}

	public function getId(int $fighter, bool $map = false): string {
		return $this->id . '-' . ($map ? $this->fighterIndex[$fighter] + 1 : ++$fighter);
	}

	public function degradeGear(Commodity $gear, Commodity $degradedGear): void {
		/** @noinspection PhpStrictComparisonWithOperandsOfDifferentTypesInspection */
		if ($this->Weapon() === $gear) {
			/** @var Weapon $degradedGear */
			$this->weapon = $degradedGear;
		} elseif ($this->Armor() === $gear) {
			/** @var Protection $degradedGear */
			$this->armor = $degradedGear;
		} elseif ($this->Shield() === $gear) {
			/** @var Shield $degradedGear */
			$this->shield = $degradedGear;
		}
	}

	/**
	 * Receive an attack from an assaulting attacker and return the damage done to the defending fighter.
	 */
	public function assault(Charge $charge): int {
		$fighter   = $charge->Defender();
		$attacker  = $charge->Attacking();
		$assaulter = $charge->Attacker();
		$health    = $this->fighter($fighter)->health;
		$damage    = $attacker->attack->perform($assaulter, $this, $fighter);
		if ($damage > 0) {
			if ($health > $damage) {
				$this->fighter($fighter)->health -= $damage;
			} else {
				$this->fighter($fighter)->health = 0;
			}
			Lemuria::Log()->debug('Fighter ' . $attacker->getId($assaulter, true) . ' deals ' . $damage . ' damage to enemy ' . $this->getId($fighter, true) . '.');
		}
		if (is_int($damage)) {
			BattleLog::getInstance()->add(new AssaultHitMessage($attacker->getId($assaulter, true), $this->getId($fighter, true), $damage));
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
		$this->fighterIndex = array_keys($this->fighters);
		return $this;
	}

	public function hasDied(int $index): Combatant {
		unset($this->fighters[$index]);
		$this->fighterIndex = array_keys($this->fighters);
		return $this;
	}

	public function split(int $size): Combatant {
		if ($size >= $this->Size()) {
			throw new LemuriaException('Split would result in empty combatant.');
		}

		$newDistribution = new Distribution();
		foreach ($this->distribution as $quantity /* @var Quantity $quantity */) {
			$count = (int)round($size * $quantity->Count() / $this->Size());
			$newDistribution->add(new Quantity($quantity->Commodity(), $count));
		}
		foreach ($newDistribution as $quantity /* @var Quantity $quantity */) {
			$this->distribution->remove(new Quantity($quantity->Commodity(), $quantity->Count()));
		}
		$this->distribution->setSize($this->Size() - $size);
		$newDistribution->setSize($size);

		$newCombatant = new Combatant($this->army, $this->unit);
		$newCombatant->setBattleRow(BattleRow::FRONT);

		$newCombatant->distribution = $newDistribution;
		$newCombatant->fighters     = array_splice($this->fighters, -$size, $size);
		$newCombatant->fighterIndex = array_keys($newCombatant->fighters);
		$this->fighterIndex         = array_keys($this->fighters);
		$newCombatant->initWeaponSkill();
		$newCombatant->initShieldAndArmor();

		return $newCombatant;
	}

	public function unsetFeatures(array $features): void {
		foreach ($features as $feature) {
			foreach (array_keys($this->fighters) as $i) {
				$this->fighters[$i]->setFeature($feature, false);
			}
		}
	}

	protected function initWeaponSkill(): void {
		if ($this->distribution) {
			$this->weaponSkill = $this->getWeaponSkill();
		}
	}

	protected function getWeaponSkill(): WeaponSkill {
		$calculus = new Calculus($this->unit);
		$isMelee  = $this->battleRow !== BattleRow::BACK;
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
		$skill  = $weaponSkill->Skill()->Talent();
		$talent = $skill::class;
		$race   = $this->unit->Race();
		if ($talent === Fistfight::class) {
			if ($race instanceof Monster) {
				$this->weapon = $race->Weapon();
				if ($this->weapon instanceof NativeMelee) {
					$weaponSkill->Skill()->addItem($this->weapon->Ability());
				}
				return true;
			}
			$this->weapon = self::createWeapon(Fists::class);
			return true;
		}
		if ($talent === Stoning::class) {
			if ($race instanceof Monster) {
				$this->weapon = $race->Weapon();
				if ($this->weapon instanceof NativeDistant) {
					$weaponSkill->Skill()->addItem($this->weapon->Ability());
				}
				return true;
			}
			$this->weapon = self::createWeapon(Dingbats::class);
			return true;
		}
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

	protected function initShieldAndArmor(): void {
		foreach ($this->distribution as $item) {
			$protection = $item->getObject();
			if ($protection instanceof Shield) {
				if (!$this->shield || $protection->Block() > $this->shield->Block()) {
					$this->shield = $protection;
				}
			} elseif ($protection instanceof Armature) {
				if (!$this->armor || $protection->Block() > $this->armor->Block()) {
					$this->armor = $protection;
				}
			}
		}
	}

	protected function initFeatures(): void {
		if ($this->unit->Race() instanceof Zombie) {
			foreach ($this->fighters as $fighter) {
				$fighter->setFeature(Feature::ZombieInfection);
			}
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
