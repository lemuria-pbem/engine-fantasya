<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Combat\WeaponSkill;
use Lemuria\Engine\Fantasya\Command\Learn;
use Lemuria\Engine\Fantasya\Command\Teach;
use Lemuria\Engine\Fantasya\Effect\PotionEffect;
use Lemuria\Exception\LemuriaException;
use Lemuria\Item;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Commodity\Camel;
use Lemuria\Model\Fantasya\Commodity\Carriage;
use Lemuria\Model\Fantasya\Commodity\Elephant;
use Lemuria\Model\Fantasya\Commodity\Griffin;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Pegasus;
use Lemuria\Model\Fantasya\Commodity\Potion\Brainpower;
use Lemuria\Model\Fantasya\Commodity\Potion\GoliathWater;
use Lemuria\Model\Fantasya\Commodity\Potion\SevenLeagueTea;
use Lemuria\Model\Fantasya\Commodity\Weapon\Dingbats;
use Lemuria\Model\Fantasya\Commodity\Weapon\Fists;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Modification;
use Lemuria\Model\Fantasya\Potion;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Fistfight;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Talent\Riding;
use Lemuria\Model\Fantasya\Talent\Stoning;
use Lemuria\Model\Fantasya\Transport;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Fantasya\Weapon;

/**
 * Helper for unit calculations.
 */
final class Calculus
{
	use BuilderTrait;

	private ?Learn $student = null;

	/**
	 * @var array(int=>Teach)
	 */
	private array $teachers = [];

	public function __construct(private Unit $unit) {
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
	#[Pure] public function getStudent(): ?Learn {
		return $this->student;
	}

	/**
	 * Get teachers.
	 *
	 * @return array(int=>Teach)
	 */
	#[Pure] public function getTeachers(): array {
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
		$carriage     = $inventory[Carriage::class] ?? null;
		$horse        = $inventory[Horse::class] ?? null;
		$camel        = $inventory[Camel::class] ?? null;
		$elephant     = $inventory[Elephant::class] ?? null;
		$griffin      = $inventory[Griffin::class] ?? null;
		$pegasus      = $inventory[Pegasus::class] ?? null;
		$weight       = $this->weight($this->unit->Weight(), [$carriage, $horse, $camel, $elephant, $griffin, $pegasus]);

		$ride       = $this->transport($carriage) + $this->transport($camel) + $this->transport($elephant);
		$ride      += $carriage ? $this->transport($horse, $carriage->Count() * 2) : $this->transport($horse);
		$fly        = $this->transport($griffin) + $this->transport($pegasus);
		$rideFly    = $ride + $fly;
		$walk       = $carriage ? $rideFly : $payload + $rideFly;
		$riding     = $size * $this->knowledge(Riding::class)->Level();
		$boostSize  = $this->hasApplied(SevenLeagueTea::class)?->Count() * SevenLeagueTea::PERSONS;
		$speedBoost = ($boostSize >= $size ? 2 : 1) * $race->Speed();

		if ($carriage) {
			$cars        = $carriage->Count();
			$speed       = $this->speed([$carriage, $horse, $camel, $elephant, $griffin, $pegasus]);
			$animals     = [$horse, $camel, $elephant, $griffin, $pegasus];
			$talentDrive = $this->talent($animals, $size, true, $cars);
			if ($riding >= $talentDrive) {
				return new Capacity($walk, $rideFly, Capacity::DRIVE, $weight, $speed, [$talentDrive, $talentDrive], $speedBoost);
			}
		}
		if ($fly > 0 && !$horse && !$camel && !$elephant) {
			$animals    = [$griffin, $pegasus];
			$speed      = $this->speed($animals);
			$talentFly  = $this->talent($animals, $size, true);
			$talentWalk = $this->talent($animals, $size);
			if ($riding >= $talentFly) {
				return new Capacity($walk, $fly, Capacity::FLY, $weight, $speed, [$talentFly, $talentWalk], $speedBoost);
			}
		}
		if ($rideFly > 0) {
			$speed      = $this->speed([$horse, $camel, $elephant]);
			$animals    = [$horse, $camel, $elephant, $griffin, $pegasus];
			$talentRide = $this->talent($animals, $size, true);
			$talentWalk = $this->talent($animals, $size);
			if ($riding >= $talentRide) {
				return new Capacity($walk, $rideFly, Capacity::RIDE, $weight, $speed, [$talentRide, $talentWalk], $speedBoost);
			}
		}
		$weight -= $size * $race->Weight();
		return new Capacity($walk, $rideFly, Capacity::WALK, $weight, $speedBoost);
	}

	/**
	 * Get a unit's hitpoints.
	 */
	public function hitpoints(): int {
		//TODO: Calculate bonus of Ausdauer.
		return $this->unit->Race()->Hitpoints();
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

		$experience = 0;
		if ($this->unit->Size() > 0) {
			$ability = $this->unit->Knowledge()->offsetGet($talent);
			if ($ability instanceof Ability) {
				$modification = $this->unit->Race()->Modifications()->offsetGet($talent);
				if ($modification instanceof Modification) {
					return $modification->getModified($ability);
				}
				$experience = $ability->Experience();
			}
		}
		return new Ability($talent, $experience);
	}

	/**
	 * Get learning progress.
	 */
	public function progress(Talent $talent): Ability {
		$teachBonus = 0.0;
		foreach ($this->teachers as $teach /* @var Teach $teach */) {
			$teachBonus += $teach->getBonus();
		}
		$teachBonus = min(1.0, $teachBonus);
		$brainpower = $this->hasApplied(Brainpower::class);
		$count      = $brainpower?->Count();
		if ($brainpower && $count) {
			$boost       = min(1.0, $count * Brainpower::PERSONS / $this->unit->Size());
			$probability = 1.25 * (1.0 + $boost);
		} else {
			$probability = rand(750, 1250) / 1000;
		}
		$progress = (int)round(100 * ($probability + $teachBonus));
		return new Ability($talent, $progress);
	}

	/**
	 * Calculate available fighting abilities for the unit's talents and inventory.
	 *
	 * @return WeaponSkill[]
	 */
	public function weaponSkill(): array {
		$fistfight = $this->knowledge(Fistfight::class);
		$fists     = new Quantity(self::createCommodity(Fists::class), $this->unit->Size());
		$stoning   = $this->knowledge(Stoning::class);
		$dingbats  = new Quantity(self::createCommodity(Dingbats::class), $this->unit->Size());
		$skills    = [new WeaponSkill($fistfight, $fists), new WeaponSkill($stoning, $dingbats)];
		$order     = [0, 0];

		foreach ($this->unit->Inventory() as $item /* @var Quantity $item */) {
			$commodity = $item->Commodity();
			if ($commodity instanceof Weapon) {
				$weaponSkill = $commodity->getSkill()->Talent();
				$skill       = $this->knowledge($weaponSkill::class);
				$experience  = $skill->Experience();
				if ($experience > 0) {
					$skills[] = new WeaponSkill($skill, $item);
					$order[]  = $experience;
				}
			}
		}

		arsort($order);
		$weaponSkills = [];
		foreach (array_keys($order) as $i) {
			$weaponSkills[] = $skills[$i];
		}
		return $weaponSkills;
	}

	/**
	 * Check if this unit can discover given unit.
	 */
	public function canDiscover(Unit $unit): bool {
		if ($unit->Construction() || $unit->Vessel()) {
			return true;
		}
		if (!$unit->IsHiding()) {
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

	#[Pure] private function transport(?Item $quantity, int $reduceBy = 0): int {
		$transport = $quantity?->getObject();
		if ($transport instanceof Transport) {
			return max($quantity->Count() - $reduceBy, 0) * $transport->Payload();
		}
		return 0;
	}

	#[Pure] private function weight(int $total, array $goods): int {
		foreach ($goods as $quantity /* @var Quantity $quantity */) {
			if ($quantity) {
				$total -= $quantity->Weight();
			}
		}
		return $total;
	}

	#[Pure] private function speed(array $transports): int {
		$speed = PHP_INT_MAX;
		foreach ($transports as $item /* @var Item $item */) {
			$transport = $item?->getObject();
			if ($transport instanceof Transport) {
				$speed = min($speed, $transport->Speed());
			}
		}
		return $speed < PHP_INT_MAX ? $speed : 0;
	}

	/**
	 * @noinspection PhpMissingBreakStatementInspection
	 */
	#[Pure] private function talent(array $transports, int $size, bool $max = false,
		                            int $carriage = 0): int {
		$talent = 0;
		foreach ($transports as $item /* @var Item $item */) {
			if ($item) {
				$transport = $item->getObject();
				$count     = $item->Count();
				switch ($transport::class) {
					case Horse::class :
						$count = max($count, $carriage * 2);
					case Camel::class :
					case Pegasus::class :
						if ($max) {
							$talent += $count;
						} elseif ($count > $size) {
							$talent += $count - $size;
						}
						break;
					case Elephant::class :
						$talent += $count * 2;
						break;
					case Griffin::class :
						$talent += $count * 6;
				}
			}
		}
		return $talent;
	}

	private function payload(string $class): int {
		/** @var Transport $transport */
		$transport = self::createCommodity($class);
		return $transport->Payload();
	}
}
