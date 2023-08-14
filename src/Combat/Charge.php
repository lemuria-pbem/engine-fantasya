<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Model\Fantasya\BattleSpell;
use Lemuria\Engine\Fantasya\Combat\Log\Message\AssaultGhostEnemyMessage;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Spell\GhostEnemy;

class Charge
{
	use BuilderTrait;

	protected Combatant $attackCombatant;

	protected int $attacker;

	protected Combatant $defendCombatant;

	protected int $defender;

	protected int $damage = 0;

	private static ?BattleSpell $ghostEnemy = null;

	private string $lastAttacker = '';

	private int $attackCount = 0;

	private string $lastDefender = '';

	private int $defendCount = 0;

	public function __construct() {
		if (!self::$ghostEnemy) {
			/** @var BattleSpell $spell */
			$spell            = self::createSpell(GhostEnemy::class);
			self::$ghostEnemy = $spell;
		}
	}

	public function Attacking(): Combatant {
		return $this->attackCombatant;
	}

	public function Attacker(): int {
		return $this->attacker;
	}

	public function AttackCount(): int {
		return $this->attackCount;
	}

	public function Defending(): Combatant {
		return $this->defendCombatant;
	}

	public function Defender(): int {
		return $this->defender;
	}

	public function DefendCount(): int {
		return $this->defendCount;
	}

	public function Damage(): int {
		return $this->damage;
	}

	public function setAttacking(?Combatant $combatant): static {
		if ($combatant) {
			$this->attackCombatant = $combatant;
		}
		return $this;
	}

	public function setAttacker(int $attacker): static {
		$this->attacker = $attacker;
		return $this;
	}

	public function setDefending(?Combatant $combatant): static {
		if ($combatant) {
			$this->defendCombatant = $combatant;
		}
		return $this;
	}

	public function setDefender(int $defender): static {
		$this->defender = $defender;
		return $this;
	}

	public function assault(): void {
		$this->countAttacker();
		$this->countDefender();
		$combat = $this->attackCombatant->Army()->Combat();
		$effect = $combat->getEffect(self::$ghostEnemy, $this->defendCombatant);
		if ($effect?->Spell() instanceof GhostEnemy) {
			if ($this->defendCount <= $effect->Points()) {
				$attacker = $this->attackCombatant->getId($this->attacker);
				$defender = $this->defendCombatant->getId($this->defender);
				BattleLog::getInstance()->add(new AssaultGhostEnemyMessage($attacker, $defender));
				//Lemuria::Log()->debug('Fighter ' . $attacker . ' attacks the ghost of fighter ' . $defender . '.');
				return;
			}
		}
		$this->damage += $this->defendCombatant->assault($this);
	}

	protected function countAttacker(): static {
		$next = $this->attackCombatant->getId($this->attacker);
		if ($next !== $this->lastAttacker) {
			$this->lastAttacker = $next;
			$this->attackCount++;
		}
		return $this;
	}

	protected function countDefender(): static {
		$next = $this->defendCombatant->getId($this->defender);
		if ($next !== $this->lastDefender) {
			$this->lastDefender = $next;
			$this->defendCount++;
		}
		return $this;
	}
}
