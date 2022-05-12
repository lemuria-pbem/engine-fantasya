<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use JetBrains\PhpStorm\Pure;
use function Lemuria\randChance;
use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Combat\BattleLog;
use Lemuria\Engine\Fantasya\Combat\CombatEffect;
use Lemuria\Engine\Fantasya\Combat\Log\Message\BattleSpellCastMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\BattleSpellFailedMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\BattleSpellNoAuraMessage;
use Lemuria\Engine\Fantasya\Factory\MagicTrait;
use Lemuria\Engine\Fantasya\Factory\Model\BattleSpellGrade;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\BattleSpell;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Spell;
use Lemuria\Model\Fantasya\Spell\AstralChaos;
use Lemuria\Model\Fantasya\Unit;

abstract class AbstractBattleSpell
{
	use BuilderTrait;
	use MagicTrait;

	protected array $caster;

	protected array $victim;

	protected Calculus $calculus;

	public function __construct(protected BattleSpellGrade $grade) {
	}

	public function Spell(): BattleSpell {
		return $this->grade->Spell();
	}

	public function setCaster(array $combatantRows): AbstractBattleSpell {
		$this->caster = &$combatantRows;
		return $this;
	}

	public function setVictim(array &$combatantRows): AbstractBattleSpell {
		$this->victim = &$combatantRows;
		return $this;
	}

	public function cast(Unit $unit): int {
		$this->calculus = new Calculus($unit);
		$initialGrade   = $this->grade($unit);
		$grade          = $this->modifyReliability($initialGrade);
		if ($grade > 0) {
			$this->consume($unit, $grade);
			Lemuria::Log()->debug('Unit ' . $unit . ' casts ' . $this->grade->Spell() . ' with grade ' . $grade . '.');
			BattleLog::getInstance()->add(new BattleSpellCastMessage($unit, $this->grade->Spell(), $grade));
		} elseif ($initialGrade > 0) {
			BattleLog::getInstance()->add(new BattleSpellFailedMessage($unit, $this->grade->Spell()));
		} else {
			BattleLog::getInstance()->add(new BattleSpellNoAuraMessage($unit, $this->grade->Spell()));
		}
		return $grade;
	}

	protected function grade(Unit $unit): int {
		$aura      = $unit->Aura();
		$available = $aura->Aura();
		$maximum   = (int)floor($available / $this->grade->Spell()->Aura());
		return min($maximum, $this->grade->Level());
	}

	protected function modifyReliability(int $grade): int {
		// 1. Apply effects that influence reliability and grade.
		$astralChaos = $this->getCombatEffect(self::createSpell(AstralChaos::class));
		if ($astralChaos) {
			$grade = $this->applyAstralChaos($astralChaos, $grade);
		}
		// 2. Check reliability chance.
		if (!randChance($this->grade->Reliability())) {
			$grade = 0;
		}
		return $grade;
	}

	protected function consume(Unit $unit, int $grade): void {
		$aura        = $unit->Aura();
		$available   = $aura->Aura();
		$consumption = $this->grade->Spell()->Aura();
		if ($this->isInActiveMagespire($unit)) {
			if ($grade > 1) {
				$grade = $this->reduceGrade($grade);
			} else {
				$consumption = $this->reduceConsumption($consumption);
			}
		}
		$aura->setAura($available - $grade * $consumption);
	}

	#[Pure] protected function getCombatEffect(Spell $spell): ?CombatEffect {
		if ($spell instanceof BattleSpell) {
			$effect = $this->grade->Combat()->Effects()[$spell];
			if ($effect instanceof CombatEffect) {
				return $effect;
			}
		}
		return null;
	}

	protected function applyAstralChaos(CombatEffect $astralChaos, int $grade): int {
		$chaosPoints = $astralChaos->Points();
		$spell       = $this->grade->Spell();
		$difficulty  = $grade * $spell->Difficulty();
		$aura        = $grade * $spell->Aura();
		$spellPower  = sqrt($difficulty * $aura);
		if ($spellPower <= 0.0) {
			return 0;
		}

		// 1. Reduce grade.
		$reducedPower = max(0.0, $spellPower - $chaosPoints);
		$grade        = (int)ceil($reducedPower / $spellPower);

		// 2. Decrease reliability.
		$decrease    = $chaosPoints / $spellPower;
		$reliability = max(0.0, $this->grade->Reliability() - $decrease);
		$this->grade->setReliability($reliability);

		return $grade;
	}
}
