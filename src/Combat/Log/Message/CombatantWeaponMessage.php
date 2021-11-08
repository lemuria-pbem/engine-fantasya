<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Combat\Combat;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Serializable;

class CombatantWeaponMessage extends CombatantNoWeaponMessage
{
	protected string $weapon;

	#[Pure] public function __construct(?Combatant $combatant = null) {
		parent::__construct($combatant);
		if ($combatant) {
			$this->weapon = getClass($combatant->Weapon());
		}
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->weapon = $data['weapon'];
		return $this;
	}

	#[Pure] public function getDebug(): string {
		return 'Combatant ' . $this->combatant . ', ' . $this->count .' fighters with ' . $this->weapon . ', ' . Combat::ROW_NAME[$this->battleRow] . ' row';
	}

	#[ArrayShape(['combatant' => 'null|string', 'count' => 'int', 'battleRow' => 'int', 'weapon' => 'string'])]
	#[Pure] protected function getParameters(): array {
		$parameters           = parent::getParameters();
		$parameters['weapon'] = $this->weapon;
		return $parameters;
	}

	protected function translate(string $template): string {
		$message = parent::translate($template);
		$weapon  = parent::dictionary()->get('combat.weapon.' . $this->weapon, $this->count > 1 ? 1 : 0);
		return str_replace('$weapon', $weapon, $message);
	}

	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'weapon', 'string');
	}
}
