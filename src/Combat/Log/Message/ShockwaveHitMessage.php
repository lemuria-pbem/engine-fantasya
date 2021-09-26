<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Serializable;

class ShockwaveHitMessage extends AbstractMessage
{
	protected array $simpleParameters = ['combatant', 'count'];

	#[Pure] public function __construct(protected ?string $combatant = null, protected ?int $count = null) {
	}

	#[Pure] public function getDebug(): string {
		return $this->count . ' fighters of combatant ' . $this->combatant . ' are distracted by a Shockwave.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->combatant = $data['combatant'];
		$this->count     = $data['count'];
		return $this;
	}

	#[ArrayShape(['combatant' => 'string', 'count' => 'int'])]
	#[Pure] protected function getParameters(): array {
		return ['combatant' => $this->combatant, 'count' => $this->count];
	}

	protected function translate(string $template): string {
		$message = parent::translate($template);
		$fighter = parent::dictionary()->get('combat.fighter', $this->count > 1 ? 1 : 0);
		$message = str_replace('$fighter', $fighter, $message);
		$will = parent::dictionary()->get('combat.will', $this->count > 1 ? 1 : 0);
		return str_replace('$will', $will, $message);
	}

	protected function validateSerializedData(array &$data): void {
		$this->validate($data, 'combatant', 'string');
		$this->validate($data, 'count', 'int');
	}
}
