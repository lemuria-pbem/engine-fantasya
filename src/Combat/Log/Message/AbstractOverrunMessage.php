<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Lemuria\Serializable;

abstract class AbstractOverrunMessage extends AbstractMessage
{
	public function __construct(protected ?int $additional = null) {
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->additional = $data['additional'];
		return $this;
	}

	#[ArrayShape(['additional' => 'int'])]
	#[Pure] protected function getParameters(): array {
		return ['additional' => $this->additional];
	}

	protected function validateSerializedData(array &$data): void {
		$this->validate($data, 'additional', 'int');
	}
}
