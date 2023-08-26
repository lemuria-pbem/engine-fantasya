<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use function Lemuria\randInt;
use Lemuria\Engine\Fantasya\Combat\WeaponSkill;
use Lemuria\Engine\Fantasya\Command\Learn;
use Lemuria\Engine\Fantasya\Command\Teach;
use Lemuria\Engine\Fantasya\Effect\Contagion;
use Lemuria\Engine\Fantasya\Effect\PotionEffect;
use Lemuria\Engine\Fantasya\Effect\TalentEffect;
use Lemuria\Engine\Fantasya\Effect\Unmaintained;
use Lemuria\Engine\Fantasya\Factory\GearDistribution;
use Lemuria\Engine\Fantasya\Factory\LodgingTrait;
use Lemuria\Engine\Fantasya\Factory\Model\Teacher;
use Lemuria\Engine\Fantasya\Travel\Conveyance;
use Lemuria\Engine\Fantasya\Travel\StaminaBonus;
use Lemuria\Engine\Fantasya\Travel\Trip;
use Lemuria\Engine\Fantasya\Travel\Trip\Caravan;
use Lemuria\Engine\Fantasya\Travel\Trip\Cruise;
use Lemuria\Engine\Fantasya\Travel\Trip\Drive;
use Lemuria\Engine\Fantasya\Travel\Trip\Flight;
use Lemuria\Engine\Fantasya\Travel\Trip\Ride;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Building;
use Lemuria\Model\Fantasya\Building\College;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Potion\Brainpower;
use Lemuria\Model\Fantasya\Commodity\Potion\GoliathWater;
use Lemuria\Model\Fantasya\Distribution;
use Lemuria\Model\Fantasya\DoubleAbility;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Factory\InventoryDistribution;
use Lemuria\Model\Fantasya\Modification;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Potion;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Fistfight;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Talent\Riding;
use Lemuria\Model\Fantasya\Talent\Stamina;
use Lemuria\Model\Fantasya\Talent\Stoning;
use Lemuria\Model\Fantasya\Unit;

/**
 * Helper for unit calculations.
 */
final class Calculus
{
	use BuilderTrait;
	use LodgingTrait;

	private static ?int $horsePayload = null;

	private static ?Talent $stamina = null;

	/**
	 * @var array<Learn>
	 */
	private array $students = [];

	/**
	 * @var array<int, Teach>
	 */
	private array $teachers = [];

	private ?Teacher $teacher = null;

	public function __construct(private readonly Unit $unit) {
	}

	public function Unit(): Unit {
		return $this->unit;
	}

	/**
	 * Add student status for teaching.
	 */
	public function addStudent(Learn $student): Calculus {
		$this->students[] = $student;
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
	public function getStudents(): array {
		return $this->students;
	}

	/**
	 * Get teachers.
	 *
	 * @return array<int, Teach>
	 */
	public function getTeachers(): array {
		return $this->teachers;
	}

	/**
	 * Get the Teacher object.
	 */
	public function getTeacher(): Teacher {
		if (!$this->teacher) {
			$this->teacher = new Teacher($this->unit);
		}
		return $this->teacher;
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
	 * Calculate race Ability in given Talent.
	 */
	public function ability(Talent|string $talent): Ability {
		$talent  = $this->parseTalent($talent);
		$ability = $this->unit->Knowledge()->offsetGet($talent);
		if ($ability instanceof Ability) {
			if ($ability->Level() <= 0) {
				return $ability;
			}
			$race         = $this->unit->Race();
			$modification = $race->Modifications()->offsetGet($talent);
			if ($modification instanceof Modification) {
				$ability = $modification->getModified($ability);
			}
			return $ability;
		}
		return new Ability($talent, 0);
	}

	/**
	 * Calculate Ability in given Talent.
	 */
	public function knowledge(Talent|string $talent): Ability {
		$talent = $this->parseTalent($talent);
		if ($this->unit->Size() > 0) {
			$ability = $this->unit->Knowledge()->offsetGet($talent);
			if ($ability instanceof Ability) {
				if ($ability->Level() <= 0) {
					return $ability;
				}
				if ($this->contagionEffect()?->Units()->has($this->unit->Id())) {
					return new Ability($talent, 0);
				}

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
	 * Get payload, including all bonuses and maluses.
	 */
	public function payload(int $size = 0): int {
		if ($size <= 0) {
			$size = $this->unit->Size();
		}
		$payload = $this->unit->Race()->Payload();
		$stamina = $this->knowledge($this->stamina())->Level();
		$factor  = 1.0 + StaminaBonus::factor($stamina);
		$boost   = $this->getPayloadBoost();
		return $size * (int)round($factor * $payload, -1) + $boost;
	}

	/**
	 * Get learning progress.
	 */
	public function progress(Talent $talent, float $effectivity = 1.0): Ability {
		$building     = $this->unit->Construction()?->Building();
		$isInCollege  = ($building instanceof College) && $this->isInMaintainedConstruction();
		$regularBonus = 0.0;
		$collegeBonus = 0.0;
		foreach ($this->teachers as $teach) {
			if ($isInCollege) {
				$teacher   = $teach->Unit();
				$inCollege = $teacher->Construction()?->Building() instanceof College;
				if ($inCollege) {
					$calculus = new Calculus($teacher);
					if ($calculus->isInMaintainedConstruction()) {
						$collegeBonus += $teach->getBonus();
					}
				}
			} else {
				$regularBonus += $teach->getBonus();
			}
		}
		$regularBonus = min(1.0, $regularBonus ** 2);
		$collegeBonus = 2.0 * min(1.0, $collegeBonus ** 2);
		$teachBonus   = max($regularBonus, $collegeBonus);

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
	 * Calculate available fighting abilities.
	 *
	 * @return WeaponSkill[]
	 */
	public function weaponSkill(): array {
		$skills  = [];
		$order   = [];
		$melee   = 0;
		$distant = 0;
		foreach ($this->unit->Knowledge() as $ability) {
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
	 * @return array<Distribution>
	 */
	public function gearDistribution(): array {
		$distribution = new GearDistribution($this);
		return $distribution->distribute()->getDistributions();
	}

	/**
	 * @return array<Distribution>
	 */
	public function inventoryDistribution(): array {
		$distribution = new InventoryDistribution($this->unit);
		return $distribution->distribute()->getDistributions();
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
		foreach ($region->Estate() as $construction) {
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
		foreach ($region->Residents() as $unit) {
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
		foreach (Lemuria::World()->getNeighbours($this->unit->Region()) as $region) {
			foreach ($region->Residents() as $unit) {
				if ($unit !== $this->unit && $unit->Party() === $party && $unit->Race() === $race) {
					$kinsmen->add($unit);
				}
			}
		}
		return $kinsmen;
	}

	public function getTrip(): Trip {
		if ($this->unit->Vessel()) {
			return new Cruise($this);
		}

		$conveyance = new Conveyance($this->unit);
		if ($conveyance->Griffin()) {
			return new Flight($this, $conveyance);
		}
		if ($conveyance->Carriage() || $conveyance->Catapult()) {
			return new Drive($this, $conveyance);
		}

		$size   = $this->unit->Size();
		$weight = $size * $this->unit->Race()->Weight() + $conveyance->getPayload();
		$riding = $size * $this->knowledge(Riding::class)->Level();
		$riders = $conveyance->Horse() + $conveyance->Camel() + $conveyance->Elephant() + $conveyance->WarElephant();
		if ($conveyance->Pegasus() && !$riders) {
			$flight = new Flight($this, $conveyance);
			if ($weight <= $flight->Capacity() && $riding >= $flight->Knowledge()) {
				return $flight;
			}
		}
		$ride = new Ride($this, $conveyance);
		if ($weight <= $ride->Capacity() && $riding >= $ride->Knowledge()) {
			return $ride;
		}
		return new Caravan($this, $conveyance);
	}

	private function parseTalent(Talent|string $talent): Talent {
		if (is_string($talent)) {
			$talent = self::createTalent($talent);
		}
		if (!($talent instanceof Talent)) {
			throw new LemuriaException('Invalid talent.');
		}
		return $talent;
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

	private function contagionEffect(): ?Contagion {
		$effect = new Contagion(State::getInstance());
		$effect = Lemuria::Score()->find($effect->setRegion($this->unit->Region()));
		return $effect instanceof Contagion ? $effect : null;
	}

	private function getPayloadBoost(): int {
		$boostSize = $this->hasApplied(GoliathWater::class)?->Count() * GoliathWater::PERSONS;
		if ($boostSize > 0) {
			$payloadBoost = min($this->unit->Size(), $boostSize);
			return $payloadBoost * $this->getHorsePayload();
		}
		return 0;
	}

	private function getHorsePayload(): int {
		if (self::$horsePayload === null) {
			/** @var Horse $horse */
			$horse              = self::createCommodity(Horse::class);
			self::$horsePayload = $horse->Payload();
		}
		return self::$horsePayload;
	}

	private function stamina(): Talent {
		if (!self::$stamina) {
			self::$stamina = self::createTalent(Stamina::class);
		}
		return self::$stamina;
	}
}
