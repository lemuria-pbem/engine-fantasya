<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Resources;
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

	private Resources $loss;

	/**
	 * @var Combatant[]
	 */
	private array $combatants = [];

	public function __construct(private Party $party) {
		$this->id    = ++self::$nextId;
		$this->units = new People();
		$this->loss  = new Resources();
		Lemuria::Log()->debug('New army ' . $this->id . ' for party ' . $this->party . '.');
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

	public function Loss(): Resources {
		return $this->loss;
	}

	public function add(Unit $unit): Army {
		if ($unit->Party()->Id() !== $this->party->Id()) {
			throw new LemuriaException('Only units from the same party can build an army.');
		}

		$this->units->add($unit);
		$calculus  = new Calculus($unit);
		$battleRow = Combat::getBattleRow($unit);
		foreach ($calculus->inventoryDistribution() as $distribution) {
			$combatant = new Combatant($this, $unit);
			$combatant->setBattleRow($battleRow)->setDistribution($distribution);
			$this->combatants[] = $combatant;
		}
		Lemuria::Log()->debug('Army ' . $this->id . ': Unit ' . $unit . ' (size: ' . $unit->Size() . ') forms ' . count($this->combatants) . ' combatants.');
		return $this;
	}

	public function addCombatant(Combatant $combatant): Army {
		if ($combatant->Unit()->Party()->Id() !== $this->party->Id()) {
			throw new LemuriaException('Only combatants from the same party can add to its army.');
		}

		$this->combatants[] = $combatant;
		Lemuria::Log()->debug('Combatant ' . $combatant->Id() . ' was added to army ' . $this->id . '.');
		return $this;
	}
}
