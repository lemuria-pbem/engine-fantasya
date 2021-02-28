<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Combat\WeaponSkill;
use Lemuria\Engine\Lemuria\Command\Learn;
use Lemuria\Engine\Lemuria\Command\Teach;
use Lemuria\Exception\LemuriaException;
use Lemuria\Item;
use Lemuria\Model\Lemuria\Ability;
use Lemuria\Model\Lemuria\Commodity\Camel;
use Lemuria\Model\Lemuria\Commodity\Carriage;
use Lemuria\Model\Lemuria\Commodity\Elephant;
use Lemuria\Model\Lemuria\Commodity\Griffin;
use Lemuria\Model\Lemuria\Commodity\Horse;
use Lemuria\Model\Lemuria\Commodity\Pegasus;
use Lemuria\Model\Lemuria\Commodity\Weapon\Fists;
use Lemuria\Model\Lemuria\Factory\BuilderTrait;
use Lemuria\Model\Lemuria\Modification;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Talent;
use Lemuria\Model\Lemuria\Talent\Fistfight;
use Lemuria\Model\Lemuria\Transport;
use Lemuria\Model\Lemuria\Unit;
use Lemuria\Model\Lemuria\Weapon;

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
			$ship   = $vessel->Ship();
			$weight = 0;
			foreach ($vessel->Passengers() as $unit /* @var Unit $unit */) {
				$weight += $unit->Weight();
			}
			return new Capacity(0, $ship->Payload(), Capacity::SHIP, $weight, $ship->Speed(), $ship->Crew());
		}

		$race      = $this->unit->Race();
		$size      = $this->unit->Size();
		$payload   = $size * $race->Payload();
		$inventory = $this->unit->Inventory();
		$carriage  = $inventory[Carriage::class] ?? null;
		$horse     = $inventory[Horse::class] ?? null;
		$camel     = $inventory[Camel::class] ?? null;
		$elephant  = $inventory[Elephant::class] ?? null;
		$griffin   = $inventory[Griffin::class] ?? null;
		$pegasus   = $inventory[Pegasus::class] ?? null;
		$weight    = $this->weight($this->unit->Weight(), [$carriage, $horse, $camel, $elephant, $griffin, $pegasus]);

		$ride    = $this->transport($carriage) + $this->transport($camel) + $this->transport($elephant);
		$ride   += $carriage ? $this->transport($horse, $carriage->Count() * 2) : $this->transport($horse);
		$fly     = $this->transport($griffin) + $this->transport($pegasus);
		$rideFly = $ride + $fly;
		$walk    = $payload + $rideFly;

		if ($carriage) {
			$cars        = $carriage->Count();
			$speed       = $this->speed([$carriage, $horse, $camel, $elephant, $griffin, $pegasus]);
			$animals     = [$horse, $camel, $elephant, $griffin, $pegasus];
			$talentDrive = $this->talent($animals, $size, true, $cars);
			$talentWalk  = $this->talent($animals, $size, carriage: $cars);
			return new Capacity($walk, $rideFly, Capacity::DRIVE, $weight, $speed, [$talentDrive, $talentWalk]);
		}
		if ($fly > 0 && !$horse && !$camel && !$elephant) {
			$animals    = [$griffin, $pegasus];
			$speed      = $this->speed($animals);
			$talentFly  = $this->talent($animals, $size, true);
			$talentWalk = $this->talent($animals, $size);
			return new Capacity($walk, $fly, Capacity::FLY, $weight, $speed, [$talentFly, $talentWalk]);
		}
		if ($rideFly > 0) {
			$speed      = $this->speed([$horse, $camel, $elephant]);
			$animals    = [$horse, $camel, $elephant, $griffin, $pegasus];
			$talentRide = $this->talent($animals, $size, true);
			$talentWalk = $this->talent($animals, $size);
			return new Capacity($walk, $rideFly, Capacity::RIDE, $weight, $speed, [$talentRide, $talentWalk]);
		}
		$weight -= $size * $race->Weight();
		return new Capacity($walk, $rideFly, Capacity::WALK, $weight, $race->Speed());
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
	#[Pure] public function progress(Talent $talent): Ability {
		$teachBonus = 0.0;
		foreach ($this->teachers as $teach /* @var Teach $teach */) {
			$teachBonus += $teach->getBonus();
		}
		$teachBonus  = min(1.0, $teachBonus);
		$probability = rand() / getrandmax();
		$progress    = (int)round(100 * ($probability + 0.25 + $teachBonus));
		return new Ability($talent, $progress);
	}

	/**
	 * Calculate best fighting ability for the unit's talents and inventory.
	 */
	public function weaponSkill(): WeaponSkill {
		$bestSkill = $this->knowledge(Fistfight::class);
		$weapon    = new Quantity(self::createCommodity(Fists::class), $this->unit->Size());
		foreach ($this->unit->Inventory() as $item /* @var Quantity $item */) {
			$commodity = $item->Commodity();
			if ($commodity instanceof Weapon) {
				$weaponSkill = $commodity->getSkill()->Talent();
				$skill       = $this->knowledge($weaponSkill::class);
				if ($skill->Experience() > $bestSkill->Experience()) {
					$bestSkill = $skill;
					$weapon    = $item;
				}
			}
		}
		return new WeaponSkill($bestSkill, $weapon);
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
}
