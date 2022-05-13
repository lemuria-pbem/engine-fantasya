<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Serializable;

class CombatantWeaponSplitMessage extends AbstractMessage
{
	protected array $simpleParameters = ['combatant', 'count', 'newCombatant'];

	protected string $combatant;

	protected string $newCombatant;

	#[Pure] public function __construct(?Combatant $combatant = null, protected ?int $count = null,
										?Combatant $newCombatant = null) {
		if ($combatant) {
			$this->combatant = $combatant->Id();
		}
		if ($newCombatant) {
			$this->newCombatant = $newCombatant->Id();
		}
	}

	#[Pure] public function getDebug(): string {
		return 'Combatant ' . $this->combatant . ' sends ' . $this->count . ' fighters to the new combatant ' . $this->newCombatant . '.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->combatant    = $data['combatant'];
		$this->count        = $data['count'];
		$this->newCombatant = $data['newCombatant'];
		return $this;
	}

	#[ArrayShape(['combatant' => 'string', 'count' => 'int', 'newCombatant' => 'string'])]
	#[Pure] protected function getParameters(): array {
		return ['combatant' => $this->combatant, 'count' => $this->count, 'newCombatant' => $this->newCombatant];
	}

	protected function translate(string $template): string {
		$message   = parent::translate($template);
		$fighter   = parent::dictionary()->get('combat.fighter', $this->count > 1 ? 1 : 0);
		return str_replace('$fighter', $fighter, $message);
	}

	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'combatant', 'string');
		$this->validate($data, 'count', 'int');
		$this->validate($data, 'newCombatant', 'string');
	}
}
