<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

/**
 * An army consists of all combatants from a single party.
 */
class Army
{
	private static int $nextId = 0;

	private int $id;

	private People $units;

	/**
	 * @var Combatant[]
	 */
	private array $combatants = [];

	#[Pure] public function __construct(private Party $party) {
		$this->id    = ++self::$nextId;
		$this->units = new People();
	}

	public function Id(): int {
		return $this->id;
	}

	/**
	 * @return Combatant[]
	 */
	public function Combatants(): array {
		return $this->combatants;
	}

	public function Party(): Party {
		return $this->party;
	}

	public function Units(): People {
		return $this->units;
	}

	public function add(Unit $unit): Army {
		if ($unit->Party()->Id() !== $this->party->Id()) {
			throw new LemuriaException('Only units from the same party can build an army.');
		}

		$this->units->add($unit);
		$partyUnit = $unit;
		while (true) {
			// Calculate best weapon skill.
			$calculus           = new Calculus($unit);
			$weaponSkill        = $calculus->bestWeaponSkill();
			$combatant          = new Combatant($partyUnit);
			$this->combatants[] = $combatant->setWeapon($weaponSkill);
			$combatantSize      = $combatant->Size();
			$remaining          = $unit->Size() - $combatantSize;
			if ($remaining < 0) {
				throw new LemuriaException('Remaining unit has negative size.');
			}
			if ($remaining === 0) {
				break;
			}

			// Prepare next combatant.
			$weapon   = $weaponSkill->Weapon()->Commodity();
			$nextUnit = new Unit();
			$nextUnit->setRace($unit->Race())->setSize($remaining)->Knowledge()->fill($unit->Knowledge());
			$inventory = $nextUnit->Inventory();
			foreach ($unit->Inventory() as $quantity /* @var Quantity $quantity */) {
				$commodity = $quantity->Commodity();
				$count     = $quantity->Count();
				if ($commodity === $weapon) {
					$count -= $combatantSize;
					if ($count < 0){
						throw new LemuriaException('Too many weapons were assigned to the combatant.');
					}
					if ($count === 0) {
						continue;
					}
				}
				$inventory->add(new Quantity($commodity, $count));
			}
			$unit = $nextUnit;
		}

		return $this;
	}
}
