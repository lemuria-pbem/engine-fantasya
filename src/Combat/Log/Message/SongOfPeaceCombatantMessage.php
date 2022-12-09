<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Serializable;
use Lemuria\Validate;

class SongOfPeaceCombatantMessage extends AbstractMessage
{
	private const COMBATANT = 'combatant';

	protected array $simpleParameters = [self::COMBATANT];

	protected string $combatant;

	public function __construct(?Combatant $combatant = null) {
		if ($combatant) {
			$this->combatant = $combatant->Id();
		}
	}

	public function getDebug(): string {
		return 'Combatant ' . $this->combatant . ' leaves the battlefield in peace.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->combatant = $data[self::COMBATANT];
		return $this;
	}

	protected function getParameters(): array {
		return [self::COMBATANT => $this->combatant];
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::COMBATANT, Validate::String);
	}
}
