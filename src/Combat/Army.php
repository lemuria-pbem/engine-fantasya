<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Unit;

/**
 * An army consists of all combatants from a single party.
 */
class Army
{
	public const NEUTRAL = 0;

	public const ALLY = 1;

	public const DEFENDER = 2;

	public const ATTACKER = 3;

	private static int $nextId = 0;

	private int $id;

	private People $units;

	/**
	 * @var Combatant[]
	 */
	private array $combatants = [];

	public function __construct(private Party $party) {
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
		$calculus     = new Calculus($unit);
		$weaponSkills = $calculus->weaponSkill();
		$skill        = 0;
		$remaining    = $unit->Size();
		$isMelee      = Combat::getBattleRow($unit) === Combat::FRONT;

		while ($remaining > 0) {
			$weaponSkill = $weaponSkills[$skill++];
			if ($isMelee && $weaponSkill->isMelee() || !$isMelee && $weaponSkill->isDistant() || $weaponSkill->isUnarmed()) {
				$combatant          = new Combatant($unit);
				$this->combatants[] = $combatant->setWeapon($weaponSkill);
				$remaining         -= min($combatant->Size(), $remaining);
			}
		}

		return $this;
	}
}
