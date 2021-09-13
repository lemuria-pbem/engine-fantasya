<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Serializable;

abstract class AbstractFleeFromBattleMessage extends AbstractMessage
{
	protected string $combatant;

	#[Pure] public function __construct(?Combatant $combatant = null) {
		$this->combatant = $combatant->Id();
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->combatant = $data['combatant'];
		return $this;
	}

	#[ArrayShape(['combatant' => 'string'])]
	#[Pure] protected function getParameters(): array {
		return ['combatant' => $this->combatant];
	}

	protected function validateSerializedData(array &$data): void {
		$this->validate($data, 'combatant', 'string');
	}
}
