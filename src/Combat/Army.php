<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Combat;

use Lemuria\Engine\Lemuria\Calculus;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Lemuria\Party;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Unit;

/**
 * An army consists of all combatants from a single party.
 */
class Army
{
	private Party $party;

	/**
	 * @var Combatant[]
	 */
	private array $combatants = [];

	/**
	 * Create a parties' army.
	 *
	 * @param Party $party
	 */
	public function __construct(Party $party) {
		$this->party = $party;
	}

	/**
	 * Get all combatants.
	 *
	 * @return Combatant[]
	 */
	public function Combatants(): array {
		return $this->combatants;
	}

	/**
	 * Get the party.
	 *
	 * @return Party
	 */
	public function Party(): Party {
		return $this->party;
	}

	/**
	 * Add a unit from the party.
	 *
	 * @param Unit $unit
	 * @return self
	 */
	public function add(Unit $unit): self {
		if ($unit->Party()->Id() !== $this->party->Id()) {
			throw new LemuriaException('Only units from the same party can build an army.');
		}

		$partyUnit = $unit;
		while (true) {
			// Calculate best weapon skill.
			$calculus           = new Calculus($unit);
			$weaponSkill        = $calculus->weaponSkill();
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
