<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Serializable;

class BattleExhaustionMessage extends AbstractMessage
{
	protected array $simpleParameters = ['rounds'];

	public function __construct(protected ?int $rounds = null) {
	}

	public function getDebug(): string {
		return 'Battle ended in a draw due to exhaustion (' . $this->rounds. ' rounds without damage).';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->rounds = $data['round'];
		return $this;
	}

	protected function getParameters(): array {
		return ['rounds' => $this->rounds];
	}

	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'rounds', 'int');
	}
}
