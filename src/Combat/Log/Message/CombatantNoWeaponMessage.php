<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Combat\Combat;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Serializable;

class CombatantNoWeaponMessage extends AbstractMessage
{
	protected array $simpleParameters = ['combatant', 'count'];

	protected string $combatant;

	protected int $count;

	protected int $battleRow;

	#[Pure] public function __construct(?Combatant $combatant = null) {
		if ($combatant) {
			$this->combatant = $combatant->Id();
			$this->count     = $combatant->Size();
			$this->battleRow = $combatant->BattleRow()->value;
		}
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->combatant = $data['combatant'];
		$this->count     = $data['count'];
		$this->battleRow = $data['battleRow'];
		return $this;
	}

	#[Pure] public function getDebug(): string {
		return $this->combatant . ', ' . $this->count .' fighters, ' . Combat::ROW_NAME[$this->battleRow] . ' row';
	}

	#[ArrayShape(['combatant' => 'null|string', 'count' => 'int', 'battleRow' => 'int'])]
	#[Pure] protected function getParameters(): array {
		return ['combatant' => $this->combatant, 'count' => $this->count, 'battleRow' => $this->battleRow];
	}

	protected function translate(string $template): string {
		$message   = parent::translate($template);
		$fighter   = parent::dictionary()->get('combat.fighter', $this->count > 1 ? 1 : 0);
		$message   = str_replace('$fighter', $fighter, $message);
		$battleRow = parent::dictionary()->get('battleRow.' . $this->battleRow);
		return str_replace('$battleRow', $battleRow, $message);
	}

	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'combatant', 'string');
		$this->validate($data, 'count', 'int');
		$this->validate($data, 'battleRow', 'int');
	}
}
