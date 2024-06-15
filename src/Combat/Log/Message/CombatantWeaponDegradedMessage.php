<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Engine\Fantasya\Message\Casus;

class CombatantWeaponDegradedMessage extends CombatantWeaponMessage
{
	public function getDebug(): string {
		return 'The weapon (' . $this->weapon . ') of combatant ' . $this->combatant . ' degrades.';
	}

	protected function translateWeapon(string $message): string {
		$weapon  = $this->translateSingleton($this->weapon, $this->count > 1 ? 1 : 0, Casus::Nominative);
		return str_replace('$weapon', $weapon, $message);
	}
}
