<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use function Lemuria\randInt;
use Lemuria\Engine\Fantasya\Combat\WeaponSkill;
use Lemuria\Engine\Fantasya\Command\Learn;
use Lemuria\Engine\Fantasya\Command\Teach;
use Lemuria\Engine\Fantasya\Effect\PotionEffect;
use Lemuria\Engine\Fantasya\Effect\TalentEffect;
use Lemuria\Engine\Fantasya\Effect\Unmaintained;
use Lemuria\Engine\Fantasya\Factory\LodgingTrait;
use Lemuria\Engine\Fantasya\Factory\Model\Distribution;
use Lemuria\Exception\LemuriaException;
use Lemuria\Item;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Building;
use Lemuria\Model\Fantasya\Building\College;
use Lemuria\Model\Fantasya\Commodity\Camel;
use Lemuria\Model\Fantasya\Commodity\Carriage;
use Lemuria\Model\Fantasya\Commodity\Elephant;
use Lemuria\Model\Fantasya\Commodity\Griffin;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Pegasus;
use Lemuria\Model\Fantasya\Commodity\Potion\Brainpower;
use Lemuria\Model\Fantasya\Commodity\Potion\GoliathWater;
use Lemuria\Model\Fantasya\Commodity\Potion\SevenLeagueTea;
use Lemuria\Model\Fantasya\Commodity\Weapon\Catapult;
use Lemuria\Model\Fantasya\Commodity\Weapon\WarElephant;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\DoubleAbility;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Modification;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Potion;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Race\Troll;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Fistfight;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Talent\Riding;
use Lemuria\Model\Fantasya\Talent\Stamina;
use Lemuria\Model\Fantasya\Talent\Stoning;
use Lemuria\Model\Fantasya\Transport;
use Lemuria\Model\Fantasya\Unit;

/**
 * Helper for unit calculations.
 */
final class Calculus
{
	use BuilderTrait;
	use LodgingTrait;

	private ?Learn $student = null;

	/**
	 * @var array(int=>Teach)
	 */
	private array $teachers = [];

	public function __construct(private readonly Unit $unit) {
	}

	public function Unit(): Unit {
		return $this->unit;
	}

	/**
	 * Set student status for teaching.
	 */
	public function setStudent(Learn $student): Calculus {
		$this->student = $student;
		return $this;
	}

	/**
	 * Add a teacher unit for learning.
	 */
	public function addTeacher(Teach $teacher): Calculus {
		$id                  = $teacher->Unit()->Id()->Id();
		$this->teachers[$id] = $teacher;
		return $this;
	}

	/**
	 * Get student status.
	 */
	public function getStudent(): ?Learn {
		return $this->student;
	}

	/**
	 * Get teachers.
	 *
	 * @return array(int=>Teach)
	 */
	public function getTeachers(): array {
		return $this->teachers;
	}

	/**
	 * Calculate the capacity for travelling.
	 *
	 * @return Capacity
	 */
	public function capacity(): Capacity {
		$vessel = $this->unit->Vessel();
		if ($vessel) {
			return Capacity::forVessel($vessel);
		}

		$race         = $this->unit->Race();
		$size         = $this->unit->Size();
		$racePayload  = $race->Payload();
		$boostSize    = $this->hasApplied(GoliathWater::class)?->Count() * GoliathWater::PERSONS;
		$payloadBoost = min($size, $boostSize);
		$payload      = $size * $racePayload + $payloadBoost * ($this->payload(Horse::class) - $racePayload);
		$inventory    = $this->unit->Inventory();
		$horse        = $inventory[Horse::class] ?? null;
		$camel        = $inventory[Camel::class] ?? null;
		$elephant     = $inventory[Elephant::class] ?? null;
		$warElephant  = $inventory[WarElephant::class] ?? null;
		$griffin      = $inventory[Griffin::class] ?? null;
		$pegasus      = $inventory[Pegasus::class] ?? null;

		$carriage = $inventory[Carriage::class] ?? null;
		$catapult = $inventory[Catapult::class] ?? null;
		$caCount  = $carriage?->Count() + $catapult?->Count();
		$weight   = $this->weight($this->unit->Weight(), [$carriage, $catapult, $horse, $camel, $elephant, $warElephant, $griffin, $pegasus]);

		$ride       = $this->transport($carriage) + $this->transport($catapult);
		$ride      += $this->transport($camel) + $this->transport($elephant) + $this->transport($warElephant);
		$ride      += $this->transport($horse, $caCount * 2);
		$fly        = $this->transport($griffin) + $this->transport($pegasus);
		$rideFly    = $ride + $fly;
		$walk       = $payload + $rideFly;
		$riding     = $size * $this->knowledge(Riding::class)->Level();
		$boostSize  = $this->hasApplied(SevenLeagueTea::class)?->Count() * SevenLeagueTea::PERSONS;
		$speedBoost = ($boostSize >= $size ? 2 : 1) * $race->Speed();

		if ($caCount > 0) {
			$cars        = (int)$carriage?->Count();
			$speed       = $this->speed([$carriage, $catapult, $horse, $camel, $elephant, $warElephant, $griffin, $pegasus]);
			$animals     = [$horse, $camel, $elephant, $warElephant, $griffin, $pegasus];
			$talentDrive = $this->talent($animals, $size, true, $cars);
			$horseCount  = $horse?->Count();
			if ($riding >= $talentDrive && $horseCount >= 2 * $caCount && $weight <= $rideFly && !$catapult) {
				return new Capacity($walk, $rideFly, Capacity::DRIVE, $weight, $speed, $talentDrive, $speedBoost);
			}
			$talentWalk = $this->talent($animals, $size, false, $cars);
			if ($horseCount >= 2 * $caCount && $riding >= $talentWalk) {
				$weight -= $size * $race->Weight();
				return new Capacity($walk, $rideFly, Capacity::WALK, $weight, 1, $talentWalk, $speedBoost);
			}
			if ($race instanceof Troll) {
				$needed = 2 * $caCount;
				if ($size >= $needed) {
					$riding     = $needed * $this->knowledge(Riding::class)->Level();
					$talentWalk = $this->talent($animals, $size - $needed) + $riding;
					$weight    -= $size * $race->Weight();
					return new Capacity($walk, $rideFly, Capacity::WALK, $weight, $speedBoost, $talentWalk);
				}
			}
			$walk   = $this->transport($camel) + $this->transport($elephant) + $this->transport($warElephant)+ $this->transport($horse);
			$walk  += $this->transport($griffin) + $this->transport($pegasus) + $payload;
			$weight = $this->unit->Weight() - $size * $race->Weight();
			return new Capacity($walk, $rideFly, Capacity::WALK, $weight, $speedBoost, $talentDrive);
		}
		if ($fly > 0 && !$horse && !$camel && !$elephant && !$warElephant) {
			$animals    = [$griffin, $pegasus];
			$speed      = $this->speed($animals);
			$talentFly  = $this->talent($animals, $size, true);
			$talentWalk = $this->talent($animals, $size);
			if ($riding >= $talentFly) {
				return new Capacity($walk, $fly, Capacity::FLY, $weight, $speed, [$talentFly, $talentWalk], $speedBoost);
			}
		}
		if ($rideFly > 0 && $weight <= $rideFly) {
			$speed      = $this->speed([$horse, $camel, $elephant, $warElephant]);
			$animals    = [$horse, $camel, $elephant, $warElephant, $griffin, $pegasus];
			$talentRide = $this->talent($animals, $size, true);
			$talentWalk = $this->talent($animals, $size);
			if ($riding >= $talentRide) {
				return new Capacity($walk, $rideFly, Capacity::RIDE, $weight, $speed, [$talentRide, $talentWalk], $speedBoost);
			}
		}
		$weight -= $size * $race->Weight();
		$speed   = $this->speed([$horse, $camel, $elephant, $warElephant], $race->Speed());
		$animals = [$horse, $camel, $elephant, $warElephant, $griffin, $pegasus];
		$talent  = $this->talent($animals, $size);
		return new Capacity($walk, $rideFly, Capacity::WALK, $weight, $speed, $talent, $speedBoost);
	}

	/**
	 * Get a unit's hitpoints.
	 */
	public function hitpoints(): int {
		$endurance = $this->knowledge(Stamina::class)->Level();
		$factor    = $endurance > 0 ? 1.05 ** $endurance : 1.0;
		return (int)floor($factor * $this->unit->Race()->Hitpoints());
	}

	/**
	 * Calculate Ability in given Talent.
	 */
	public function knowledge(Talent|string $talent): Ability {
		if (is_string($talent)) {
			$talent = self::createTalent($talent);
		}
		if (!($talent instanceof Talent)) {
			throw new LemuriaException('Invalid talent.');
		}

		if ($this->unit->Size() > 0) {
			$ability = $this->unit->Knowledge()->offsetGet($talent);
			if ($ability instanceof Ability) {
				$race         = $this->unit->Race();
				$modification = $race->Modifications()->offsetGet($talent);
				if ($modification instanceof Modification) {
					$ability = $modification->getModified($ability);
				}
				$modification = $race->TerrainEffect()->getEffect($this->unit->Region()->Landscape(), $talent);
				if ($modification instanceof Modification) {
					$ability = $modification->getModified($ability);
				}
				$talentEffect = $this->talentEffect($talent);
				if ($talentEffect instanceof Modification) {
					$ability = $talentEffect->getModified($ability);
				}

				if ($this->isInMaintainedConstruction()) {
					$modification = $this->unit->Construction()->Building()->BuildingEffect()->getEffect($talent);
					if ($modification instanceof Modification) {
						if ($modification instanceof DoubleAbility) {
							$lodging = $this->getLodging();
							if (!$lodging || $lodging->hasSpace($this->unit)) {
								$ability = $modification->getModified($ability);
							}
						} else {
							$ability = $modification->getModified($ability);
						}
					}
				}
				return $ability;
			}
		}
		return new Ability($talent, 0);
	}

	/**
	 * Get learning progress.
	 */
	public function progress(Talent $talent, float $effectivity = 1.0): Ability {
		$isInCollege = ($this->unit->Construction() instanceof College) && $this->isInMaintainedConstruction();
		$teachBonus  = 0.0;
		foreach ($this->teachers as $teach /* @var Teach $teach */) {
			if ($isInCollege) {
				$teacher   = $teach->Unit();
				$inCollege = $teacher->Construction() instanceof College;
				if ($inCollege) {
					$calculus = new Calculus($teacher);
					if ($calculus->isInMaintainedConstruction()) {
						$teachBonus += 2.0 * $teach->getBonus();
					}
				}
			} else {
				$teachBonus += $teach->getBonus();
			}
		}
		$teachBonus = min($isInCollege ? 2.0 : 1.0, $teachBonus);

		$brainpower = $this->hasApplied(Brainpower::class);
		$count      = $brainpower?->Count();
		$boost      = $brainpower && $count ? min(1.0, $count * Brainpower::PERSONS / $this->unit->Size()) : 0.0;

		$baseProbability = $boost > 0.0 ? 1.25 : ($isInCollege ? randInt(90, 110) / 100 : randInt(75, 125) / 100);
		$baseFactor      = $isInCollege ? 2.0 : 1.0;
		$probability     = $baseProbability * ($baseFactor + $boost);
		$progress        = (int)round(Learn::PROGRESS * (min(5.0, $probability + $teachBonus)) * $effectivity);
		return new Ability($talent, $progress);
	}

	/**
	 * Calculate available fighting abilities for the unit's talents and inventory.
	 *
	 * @return WeaponSkill[]
	 */
	public function weaponSkill(): array {
		$skills  = [];
		$order   = [];
		$melee   = 0;
		$distant = 0;
		foreach ($this->unit->Knowledge() as $ability /* @var Ability $ability */) {
			if (WeaponSkill::isSkill($ability)) {
				$skill       = $this->knowledge($ability->Talent());
				$experience  = $skill->Experience();
				if ($experience > 0) {
					$weaponSkill = new WeaponSkill($skill);
					$skills[]    = $weaponSkill;
					$order[]     = $experience;
					if ($weaponSkill->isMelee() && $experience > $melee) {
						$melee = $experience;
					}
					if ($weaponSkill->isDistant() && $experience > $distant) {
						$distant = $experience;
					}
				}
			}
		}

		$minimum  = Ability::getExperience(1);
		$skills[] = new WeaponSkill(new Ability(self::createTalent(Fistfight::class), max($minimum, $melee)));
		$order[]  = 0;
		$skills[] = new WeaponSkill(new Ability(self::createTalent(Stoning::class), max($minimum, $distant)));
		$order[]  = 0;
		arsort($order);
		$weaponSkills = [];
		foreach (array_keys($order) as $i) {
			$weaponSkills[] = $skills[$i];
		}
		return $weaponSkills;
	}

	/**
	 * @return Distribution[]
	 */
	public function inventoryDistribution(): array {
		$maxSize   = $this->unit->Size();
		$inventory = $this->unit->Inventory();
		if ($maxSize <= 1 || $inventory->isEmpty()) {
			$distribution = new Distribution();
			foreach ($inventory as $quantity /* @var Quantity $quantity */) {
				$distribution->add(new Quantity($quantity->Commodity(), $quantity->Count()));
			}
			return [$distribution->setSize($maxSize)];
		}

		$amount = [];
		foreach ($inventory as $quantity /* @var Quantity $quantity */) {
			$count = $quantity->Count();
			if (!isset($amount[$count])) {
				$amount[$count] = [];
			}
			$amount[$count][] = new Quantity($quantity->Commodity(), $count);
		}
		ksort($amount);

		$distributions = [];
		while ($maxSize > 0 && !empty($amount)) {
			reset($amount);
			$size         = key($amount);
			$take         = $size > $maxSize ? (int)floor($size / $maxSize) : 1;
			$rest         = $size - $take * $maxSize;
			$distribution = new Distribution();
			$newAmount    = $rest > 0 ? [$rest => []] : [];
			foreach (current($amount) as $quantity /* @var Quantity $quantity */) {
				$commodity = $quantity->Commodity();
				if ($rest > 0) {
					$newAmount[$rest][] = new Quantity($commodity, $rest);
				}
				$distribution->add(new Quantity($commodity, $take));
			}
			unset($amount[$size]);
			if ($rest > 0) {
				$size = $maxSize;
			}

			foreach ($amount as $next => $quantities) {
				$take = $next > $maxSize ? (int)floor($next / $maxSize) : 1;
				$rest = $next - $take * $size;
				if ($rest > 0 && !isset($newAmount[$rest])) {
					$newAmount[$rest] = [];
				}
				foreach ($quantities as $quantity /* @var Quantity $quantity */) {
					$commodity = $quantity->Commodity();
					if ($rest > 0) {
						$newAmount[$rest][] = new Quantity($commodity, $rest);
					}
					$distribution->add(new Quantity($commodity, $take));
				}
			}

			$size            = min($maxSize, $size);
			$distributions[] = $distribution->setSize($size);
			$maxSize        -= $size;
			$amount          = $newAmount;
		}

		if ($maxSize > 0) {
			$distribution    = new Distribution();
			$distributions[] = $distribution->setSize($maxSize);
		}

		return $distributions;
	}

	/**
	 * Check if this unit can discover given unit.
	 */
	public function canDiscover(Unit $unit): bool {
		if ($unit->Construction() || $unit->Vessel()) {
			return true;
		}
		if (!$unit->IsHiding() || $unit->IsGuarding()) {
			return true;
		}
		$calculus   = new self($unit);
		$camouflage = $calculus->knowledge(Camouflage::class);
		$perception = $this->knowledge(Perception::class);
		return $perception->Level() >= $camouflage->Level();
	}

	/**
	 * Get a potion effect if the unit has applied the given potion.
	 */
	public function hasApplied(Potion|string $potion): ?PotionEffect {
		if (is_string($potion)) {
			$potion = self::createCommodity($potion);
		}
		$effect = new PotionEffect(State::getInstance());
		$effect->setUnit($this->unit);
		/** @var PotionEffect $existing */
		$existing = Lemuria::Score()->find($effect);
		return $existing?->Potion() === $potion ? $existing : null;
	}

	public function hunger(Unit $unit, float $currentHunger = 0.0): int {
		$health = $unit->Health();
		if ($health >= 0.9) {
			$factor = $currentHunger < 0.5 ? 1.1 : 1.2;
		} else {
			$factor = $currentHunger < 0.5 ? 1.2 : 1.3;
		}
		return (int)round($factor * $unit->Race()->Hunger());
	}

	public function isInMaintainedConstruction(): bool {
		$construction = $this->unit->Construction();
		if ($construction) {
			$effect = new Unmaintained(State::getInstance());
			return Lemuria::Score()->find($effect->setConstruction($construction)) === null;
		}
		return false;
	}

	public function canEnter(Region $region, Building $building): bool {
		$party      = $this->unit->Party();
		$diplomacy  = $party->Diplomacy();
		foreach ($region->Estate() as $construction /* @var Construction $construction */) {
			if ($construction->Building() === $building) {
				if ($construction->Size() >= $building->UsefulSize()) {
					$inhabitants = $construction->Inhabitants();
					$owner       = $inhabitants->Owner();
					if ($owner->Party() === $party || $diplomacy->has(Relation::ENTER, $owner)) {
						$calculus = new self($owner);
						return $calculus->isInMaintainedConstruction();
					}
				}
			}
		}
		return false;
	}

	public function getKinsmen(): People {
		$kinsmen = new People();
		$region  = $this->unit->Region();
		$party   = $this->unit->Party();
		$race    = $this->unit->Race();
		foreach ($region->Residents() as $unit /* @var Unit $unit */) {
			if ($unit !== $this->unit && $unit->Party() === $party && $unit->Race() === $race) {
				$kinsmen->add($unit);
			}
		}
		return $kinsmen;
	}

	public function getRelatives(): People {
		$kinsmen = new People();
		$party   = $this->unit->Party();
		$race    = $this->unit->Race();
		foreach (Lemuria::World()->getNeighbours($this->unit->Region()) as $region /* @var Region $region */) {
			foreach ($region->Residents() as $unit /* @var Unit $unit */) {
				if ($unit !== $this->unit && $unit->Party() === $party && $unit->Race() === $race) {
					$kinsmen->add($unit);
				}
			}
		}
		return $kinsmen;
	}

	private function transport(?Item $quantity, int $reduceBy = 0): int {
		$transport = $quantity?->getObject();
		if ($transport instanceof Transport) {
			return max($quantity->Count() - $reduceBy, 0) * $transport->Payload();
		}
		return 0;
	}

	private function weight(int $total, array $goods): int {
		foreach ($goods as $quantity /* @var Quantity $quantity */) {
			if ($quantity) {
				$total -= $quantity->Weight();
			}
		}
		return $total;
	}

	private function speed(array $transports, int $speed = PHP_INT_MAX): int {
		foreach ($transports as $item /* @var Item $item */) {
			$transport = $item?->getObject();
			if ($transport instanceof Transport) {
				$speed = min($speed, $transport->Speed());
			}
		}
		return $speed < PHP_INT_MAX ? $speed : $this->unit->Race()->Speed();
	}

	/**
	 * @noinspection PhpMissingBreakStatementInspection
	 */
	private function talent(array $transports, int $size, bool $max = false, int $carriage = 0): int {
		$talent = 0;
		foreach ($transports as $item /* @var Item $item */) {
			if ($item) {
				$transport = $item->getObject();
				$count     = $item->Count();
				switch ($transport::class) {
					case Horse::class :
						$count = max($count, $carriage * 2);
					case Camel::class :
						if ($max) {
							$talent += $count;
						} elseif ($count > $size) {
							$talent += $count - $size;
						}
						break;
					case Elephant::class :
					case WarElephant::class :
						$talent += $count * 2;
						break;
					case Pegasus::class :
						$talent += $count * 3;
						break;
					case Griffin::class :
						$talent += $count * 6;
				}
			}
		}
		if ($carriage) {
			$talent = max($talent, $max ? $carriage * 2 : $carriage);
		}
		return $talent;
	}

	private function payload(string $class): int {
		/** @var Transport $transport */
		$transport = self::createCommodity($class);
		return $transport->Payload();
	}

	private function talentEffect(Talent $talent): ?Modification {
		$effect   = new TalentEffect(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setUnit($this->unit));
		if ($existing instanceof TalentEffect) {
			$modifications = $existing->Modifications();
			if (isset($modifications[$talent])) {
				return $modifications[$talent];
			}
		}
		return null;
	}
}
