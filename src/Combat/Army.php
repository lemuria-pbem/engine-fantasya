<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Command\Apply\ElixirOfPower as ElixirOfPowerEffect;
use Lemuria\Engine\Fantasya\Effect\PotionEffect;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Potion\BerserkBlood;
use Lemuria\Model\Fantasya\Commodity\Potion\ElixirOfPower;
use Lemuria\Model\Fantasya\Commodity\Potion\HealingPotion;
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
		// Lemuria::Log()->debug('New army ' . $this->id . ' for party ' . $this->party . '.');
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
		$potion    = $this->getPotionEffect($calculus);
		$remaining = $potion ? $this->getPotionFighters($potion) : 0;
		foreach ($calculus->inventoryDistribution() as $distribution) {
			$combatant = new Combatant($this, $unit);
			$combatant->setBattleRow($battleRow)->setDistribution($distribution);
			$this->combatants[] = $combatant;
			$this->applyPotionEffect($calculus, $combatant, $potion, $remaining);
		}
		// Lemuria::Log()->debug('Army ' . $this->id . ': Unit ' . $unit . ' (size: ' . $unit->Size() . ') forms ' . count($this->combatants) . ' combatants.');
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

	protected function getPotionEffect(Calculus $calculus): ?PotionEffect {
		$effect = $calculus->hasApplied(BerserkBlood::class);
		if ($effect) {
			return $effect;
		}
		$effect = $calculus->hasApplied(ElixirOfPower::class);
		if ($effect) {
			return $effect;
		}
		$effect = $calculus->hasApplied(HealingPotion::class);
		if ($effect) {
			return $effect;
		}
		return null;
	}

	#[Pure] protected function getPotionFighters(PotionEffect $effect): int {
		$potion = $effect->Potion();
		if ($potion instanceof BerserkBlood) {
			return $effect->Count() * BerserkBlood::PERSONS;
		}
		if ($potion instanceof ElixirOfPower) {
			return $effect->Count() * ElixirOfPower::PERSONS;
		}
		return $effect->Count();
	}

	protected function applyPotionEffect(Calculus $calculus, Combatant $combatant, PotionEffect $potion, int $remaining): int {
		if ($remaining > 0) {
			$i        = 0;
			$n        = $combatant->Size();
			$fighters = min($remaining, $n);
			$bonus    = 0;
			if ($potion->Potion() instanceof ElixirOfPower) {
				$bonus = (int)floor(ElixirOfPowerEffect::BONUS * $calculus->hitpoints());
			}
			Lemuria::Log()->debug('Combatant ' . $combatant->Id() . ' uses ' . $potion . ' effect for ' . $fighters . ' fighter.');
			while ($remaining > 0 && $i++ < $n) {
				$combatant->fighters[$i]->health += $bonus;
				$combatant->fighters[$i]->potion  = $potion->Potion();
				$remaining--;
			}
		}
		return $remaining;
	}
}
