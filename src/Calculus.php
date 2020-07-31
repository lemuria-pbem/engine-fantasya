<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use Lemuria\Engine\Lemuria\Combat\WeaponSkill;
use Lemuria\Engine\Lemuria\Command\Learn;
use Lemuria\Engine\Lemuria\Command\Teach;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Lemuria\Ability;
use Lemuria\Model\Lemuria\Commodity\Weapon\Fists;
use Lemuria\Model\Lemuria\Factory\BuilderTrait;
use Lemuria\Model\Lemuria\Modification;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Talent;
use Lemuria\Model\Lemuria\Talent\Fistfight;
use Lemuria\Model\Lemuria\Unit;
use Lemuria\Model\Lemuria\Weapon;

/**
 * Helper for talent calculations.
 */
final class Calculus
{
	use BuilderTrait;

	private Unit $unit;

	private ?Learn $student = null;

	/**
	 * @var array(int=>Teach)
	 */
	private array $teachers = [];

	/**
	 * Create new calculus.
	 *
	 * @param Unit $unit
	 */
	public function __construct(Unit $unit) {
		$this->unit = $unit;
	}

	/**
	 * Set student status for teaching.
	 *
	 * @param Learn $student
	 * @return Calculus
	 */
	public function setStudent(Learn $student): self {
		$this->student = $student;
		return $this;
	}

	/**
	 * Add a teacher unit for learning.
	 *
	 * @param Teach $teacher
	 * @return Calculus
	 */
	public function addTeacher(Teach $teacher): self {
		$id                  = $teacher->Unit()->Id()->Id();
		$this->teachers[$id] = $teacher;
		return $this;
	}

	/**
	 * Get student status.
	 *
	 * @return Learn
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
	 * Calculate Ability in given Talent.
	 *
	 * @param Talent|string $talent
	 * @return Ability
	 */
	public function knowledge($talent): Ability {
		if (is_string($talent)) {
			$talent = self::createTalent($talent);
		}
		if (!($talent instanceof Talent)) {
			throw new LemuriaException('Invalid talent.');
		}

		$experience = 0;
		$ability    = $this->unit->Knowledge()->offsetGet($talent);
		if ($ability instanceof Ability) {
			$modification = $this->unit->Race()->Modifications()->offsetGet($talent);
			if ($modification instanceof Modification) {
				return $modification->getModified($ability);
			}
			$experience = $ability->Experience();
		}
		return new Ability($talent, $experience);
	}

	/**
	 * Get learning progress.
	 *
	 * @param Talent $talent
	 * @return Ability
	 */
	public function progress(Talent $talent): Ability {
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
	 *
	 * @return WeaponSkill
	 */
	public function weaponSkill(): WeaponSkill {
		$bestSkill = $this->knowledge(Fistfight::class);
		$weapon    = new Quantity(self::createCommodity(Fists::class), $this->unit->Size());
		foreach ($this->unit->Inventory() as $item /* @var Quantity $item */) {
			$commodity = $item->Commodity();
			if ($commodity instanceof Weapon) {
				$weaponSkill = $commodity->getSkill()->Talent();
				$skill       = $this->knowledge(get_class($weaponSkill));
				if ($skill->Experience() > $bestSkill->Experience()) {
					$bestSkill = $skill;
					$weapon    = $item;
				}
			}
		}
		return new WeaponSkill($bestSkill, $weapon);
	}
}
