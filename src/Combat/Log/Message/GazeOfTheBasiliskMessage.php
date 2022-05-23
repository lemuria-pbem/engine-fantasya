<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Serializable;

class GazeOfTheBasiliskMessage extends AbstractMessage
{
	protected array $simpleParameters = ['attacker'];

	public function __construct(protected ?string $attacker = null) {
	}

	public function getDebug(): string {
		return $this->attacker . ' is petrified by Gaze of the Basilisk.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->attacker = $data['attacker'];
		return $this;
	}

	protected function getParameters(): array {
		return ['attacker' => $this->attacker];
	}

	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'attacker', 'string');
	}
}
