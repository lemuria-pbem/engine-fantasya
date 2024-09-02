<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Validate;

/**
 * @deprecated This message is not used anymore since version 1.5.26.
 */
class SongOfPeaceFighterMessage extends SongOfPeaceCombatantMessage
{
	private const string FIGHTER = 'fighter';

	public function __construct(?Combatant $combatant = null, protected ?int $fighter = null) {
		parent::__construct($combatant);
		$this->simpleParameters[] = self::FIGHTER;
	}

	public function getDebug(): string {
		return $this->fighter . ' fighters of combatant ' . $this->combatant . ' leave the battlefield in peace.';
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->fighter = $data[self::FIGHTER];
		return $this;
	}

	protected function getParameters(): array {
		$parameters = parent::getParameters();
		$parameters[self::FIGHTER] = $this->fighter;
		return $parameters;
	}

	protected function translate(string $template): string {
		$message = parent::translate($template);
		$leave   = $this->dictionary->get('combat.leave', $this->fighter === 1 ? 0 : 1);
		return str_replace('$leave', $leave, $message);
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::FIGHTER, Validate::Int);
	}
}
