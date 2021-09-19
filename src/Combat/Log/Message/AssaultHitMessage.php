<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Serializable;

class AssaultHitMessage extends AbstractMessage
{
	#[Pure] public function __construct(protected ?string $attacker = null, protected ?string $defender = null,
										protected ?int $damage = null) {
	}

	#[Pure] public function getDebug(): string {
		return 'Fighter ' . $this->attacker . ' deals ' . $this->damage . ' damage to enemy ' . $this->defender . '.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->attacker = $data['attacker'];
		$this->defender = $data['defender'];
		$this->damage   = $data['damage'];
		return $this;
	}

	#[ArrayShape(['attacker' => 'string', 'defender' => 'string', 'damage' => 'int'])]
	#[Pure] protected function getParameters(): array {
		return ['attacker' => $this->attacker, 'defender' => $this->defender, 'damage' => $this->damage];
	}

	protected function validateSerializedData(array &$data): void {
		$this->validate($data, 'attacker', 'string');
		$this->validate($data, 'defender', 'string');
		$this->validate($data, 'damage', 'int');
	}
}