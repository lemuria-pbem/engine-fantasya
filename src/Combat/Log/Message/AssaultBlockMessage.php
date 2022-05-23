<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Serializable;

class AssaultBlockMessage extends AbstractMessage
{
	protected array $simpleParameters = ['attacker', 'defender'];

	public function __construct(protected ?string $attacker = null, protected ?string $defender = null) {
	}

	public function getDebug(): string {
		return $this->defender . ' blocks attack from ' . $this->attacker . '.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->attacker = $data['attacker'];
		$this->defender = $data['defender'];
		return $this;
	}

	protected function getParameters(): array {
		return ['attacker' => $this->attacker, 'defender' => $this->defender];
	}

	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'attacker', 'string');
		$this->validate($data, 'defender', 'string');
	}
}
