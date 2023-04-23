<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Combat\Combat;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Serializable;
use Lemuria\Validate;

class CombatantWeaponMessage extends CombatantNoWeaponMessage
{
	private const WEAPON = 'weapon';

	protected string $weapon;

	public function __construct(?Combatant $combatant = null) {
		parent::__construct($combatant);
		if ($combatant) {
			$this->weapon = getClass($combatant->Weapon());
		}
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->weapon = $data[self::WEAPON];
		return $this;
	}

	public function getDebug(): string {
		return 'Combatant ' . $this->combatant . ', ' . $this->count .' fighters with ' . $this->weapon . ', ' . Combat::ROW_NAME[$this->battleRow] . ' row';
	}

	protected function getParameters(): array {
		$parameters           = parent::getParameters();
		$parameters[self::WEAPON] = $this->weapon;
		return $parameters;
	}

	protected function translate(string $template): string {
		$message = parent::translate($template);
		$weapon  = $this->translateSingleton($this->weapon, $this->count > 1 ? 1 : 0, Casus::Dative);
		return str_replace('$weapon', $weapon, $message);
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::WEAPON, Validate::String);
	}
}
