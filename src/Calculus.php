<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Combat\WeaponSkill;
use Lemuria\Engine\Fantasya\Command\Learn;
use Lemuria\Engine\Fantasya\Command\Teach;
use Lemuria\Engine\Fantasya\Effect\PotionEffect;
use Lemuria\Engine\Fantasya\Effect\Unmaintained;
use Lemuria\Engine\Fantasya\Factory\Model\Distribution;
use Lemuria\Exception\LemuriaException;
use Lemuria\Item;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Camel;
use Lemuria\Model\Fantasya\Commodity\Carriage;
use Lemuria\Model\Fantasya\Commodity\Elephant;
use Lemuria\Model\Fantasya\Commodity\Griffin;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Pegasus;
use Lemuria\Model\Fantasya\Commodity\Potion\Brainpower;
use Lemuria\Model\Fantasya\Commodity\Potion\GoliathWater;
use Lemuria\Model\Fantasya\Commodity\Potion\SevenLeagueTea;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Modification;
use Lemuria\Model\Fantasya\Potion;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Race\Troll;
use Lemuria\Model\Fantasya\Resources;
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
			$horseCount  = $horse?->Count();
			if ($riding >= $talentDrive && $horseCount >= 2 * $cars) {
				return new Capacity($walk, $rideFly, Capacity::DRIVE, $weight, $speed, $talentDrive, $speedBoost);
			}
			$talentWalk = $this->talent($animals, $size, false, $cars);
			if ($riding >= $talentWalk) {
				return new Capacity($walk, $rideFly, Capacity::WALK, $weight, 1, $talentWalk, $speedBoost);
			}
			if ($race instanceof Troll) {
				$needed = 2 * $cars;
				if ($size >= $needed) {
					$riding     = $needed * $this->knowledge(Riding::class)->Level();
					$talentWalk = $this->talent($animals, $size - $needed) + $riding;
					$weight    -= $size * $race->Weight();
					return new Capacity($walk, $rideFly, Capacity::WALK, $weight, $speedBoost, $talentWalk);
				}
			}
			$walk   = $this->transport($camel) + $this->transport($elephant) + $this->transport($horse);
			$walk  += $this->transport($griffin) + $this->transport($pegasus) + $payload;
			$weight = $this->unit->Weight() - $size * $race->Weight();
			return new Capacity($walk, $rideFly, Capacity::WALK, $weight, $speedBoost, $talentDrive);
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
		if ($rideFly > 0 && $weight <= $rideFly) {
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

				if ($this->isInMaintainedConstruction()) {
					$modification = $this->unit->Construction()->Building()->BuildingEffect()->getEffect($talent);
					if ($modification instanceof Modification) {
						$ability = $modification->getModified($ability);
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
		$stoning   = $this->knowledge(Stoning::class);
		$skills    = [new WeaponSkill($fistfight), new WeaponSkill($stoning)];
		$order     = [0, 0];

		foreach ($this->unit->Knowledge() as $ability /* @var Ability $ability */) {
			$talent = $ability->Talent();
			if (WeaponSkill::isSkill($talent)) {
				$skill       = $this->knowledge($talent);
				$experience  = $skill->Experience();
				if ($experience > 0) {
					$skills[] = new WeaponSkill($skill);
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
	 * @return Distribution[]
	 */
	public function inventoryDistribution(): array {
		$maxSize   = $this->unit->Size();
		$inventory = $this->unit->Inventory();
		if (empty($inventory)) {
			$distribution = new Distribution();
			return [$distribution->setSize($maxSize)];
		}

		$distributions = [];
		$amount        = [];
		$excess        = new Resources();
		foreach ($inventory as $quantity /* @var Quantity $quantity */) {
			$commodity = $quantity->Commodity();
			$count     = $quantity->Count();
			$surplus   = $count - $maxSize;
			if ($surplus > 0) {
				$excess->add(new Quantity($commodity, $surplus));
				$count = $maxSize;
			}
			if (!isset($amount[$count])) {
				$amount[$count] = [];
			}
			$amount[$count][] = $commodity;
		}
		ksort($amount);

		$distributed = 0;
		while (!empty($amount)) {
			$distribution = new Distribution();
			reset($amount);
			$first     = key($amount);
			$remaining = $first - $distributed;
			foreach ($amount as $count => $commodities) {
				$remaining   = $count - $distributed;
				$distributed = $remaining;
				foreach ($commodities as $commodity/* @var Commodity $commodity */) {
					$distribution->add(new Quantity($commodity, $remaining));
				}
			}
			$distribution->setSize($remaining);
			$distributions[] = $distribution;
			$distributed    += $remaining;
			unset($amount[$first]);
		}

		foreach ($excess as $quantity /* @var Quantity $quantity */) {
			$count     = $quantity->Count();
			$bonus     = (int)floor($count / $maxSize);
			$remaining = $count % $maxSize;
			foreach ($distributions as $distribution /* @var Distribution $distribution */) {
				$amount = $bonus + ($remaining-- > 0 ? 1 : 0);
				if ($amount <= 0) {
					break;
				}
				$distribution->add(new Quantity($quantity->Commodity(), $amount));
			}
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

	#[Pure] public function hunger(Unit $unit, float $currentHunger = 0.0): int {
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
	#[Pure] private function talent(array $transports, int $size, bool $max = false, int $carriage = 0): int {
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
		if ($carriage) {
			$talent = max($talent, $carriage * 2);
		}
		return $talent;
	}

	private function payload(string $class): int {
		/** @var Transport $transport */
		$transport = self::createCommodity($class);
		return $transport->Payload();
	}
}
