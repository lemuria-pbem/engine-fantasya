<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Serializable;

class CombatRoundMessage extends AbstractMessage
{
	protected array $simpleParameters = ['round'];

	public function __construct(protected ?int $round = null) {
	}

	#[Pure] public function getDebug(): string {
		return 'Combat round ' . $this->round . ' starts.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->round = $data['round'];
		return $this;
	}

	#[ArrayShape(['round' => 'int'])]
	#[Pure] protected function getParameters(): array {
		return ['round' => $this->round];
	}

	protected function validateSerializedData(array &$data): void {
		$this->validate($data, 'round', 'int');
	}
}
