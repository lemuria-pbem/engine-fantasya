<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

/**
 * An army consists of all combatants from a single party.
 */
class Army
{
	/**
	 * @var Combatant[]
	 */
	private array $combatants = [];

	/**
	 * Create a parties' army.
	 */
	#[Pure] public function __construct(private Party $party) {
	}

	/**
	 * Get all combatants.
	 *
	 * @return Combatant[]
	 */
	#[Pure] public function Combatants(): array {
		return $this->combatants;
	}

	#[Pure] public function Party(): Party {
		return $this->party;
	}

	/**
	 * Add a unit from the party.
	 */
	public function add(Unit $unit): Army {
		if ($unit->Party()->Id() !== $this->party->Id()) {
			throw new LemuriaException('Only units from the same party can build an army.');
		}

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
