<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Serializable;

abstract class AbstractFleeFromBattleMessage extends AbstractMessage
{
	protected array $simpleParameters = ['combatant'];

	protected string $combatant;

	public function __construct(?Combatant $combatant = null) {
		if ($combatant) {
			$this->combatant = $combatant->Id();
		}
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->combatant = $data['combatant'];
		return $this;
	}

	protected function getParameters(): array {
		return ['combatant' => $this->combatant];
	}

	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'combatant', 'string');
	}
}
