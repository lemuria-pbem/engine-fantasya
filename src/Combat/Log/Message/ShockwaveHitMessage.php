<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Serializable;

class ShockwaveHitMessage extends AbstractMessage
{
	protected array $simpleParameters = ['combatant', 'count'];

	public function __construct(protected ?string $combatant = null, protected ?int $count = null) {
	}

	public function getDebug(): string {
		return $this->count . ' fighters of combatant ' . $this->combatant . ' are distracted by a Shockwave.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->combatant = $data['combatant'];
		$this->count     = $data['count'];
		return $this;
	}

	protected function getParameters(): array {
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
		parent::validateSerializedData($data);
		$this->validate($data, 'combatant', 'string');
		$this->validate($data, 'count', 'int');
	}
}
