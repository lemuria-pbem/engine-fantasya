<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Serializable;

class CombatRoundMessage extends AbstractMessage
{
	protected array $simpleParameters = ['round'];

	public function __construct(protected ?int $round = null) {
	}

	public function getDebug(): string {
		return 'Combat round ' . $this->round . ' starts.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->round = $data['round'];
		return $this;
	}

	protected function getParameters(): array {
		return ['round' => $this->round];
	}

	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'round', 'int');
	}
}
