<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Serializable;

class SongOfPeaceFighterMessage extends SongOfPeaceCombatantMessage
{
	#[Pure] public function __construct(?Combatant $combatant = null, protected ?int $fighter = null) {
		parent::__construct($combatant);
		$this->simpleParameters[] = 'fighter';
	}

	public function getDebug(): string {
		return $this->fighter . ' fighters of combatant ' . $this->combatant . ' leave the battlefield in peace.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->fighter = $data['fighter'];
		return $this;
	}

	#[ArrayShape(['combatant' => 'string', 'fighter' => 'int'])]
	#[Pure] protected function getParameters(): array {
		$parameters = parent::getParameters();
		$parameters['fighter'] = $this->fighter;
		return $parameters;
	}

	protected function translate(string $template): string {
		$message = parent::translate($template);
		$leave   = parent::dictionary()->get('combat.leave', $this->fighter === 1 ? 0 : 1);
		return str_replace('$leave', $leave, $message);
	}

	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'fighter', 'int');
	}
}
