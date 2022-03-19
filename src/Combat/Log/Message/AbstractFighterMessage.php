<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Serializable;

abstract class AbstractFighterMessage extends AbstractMessage
{
	protected array $simpleParameters = ['fighter'];

	#[Pure] public function __construct(protected ?string $fighter = null) {
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->fighter = $data['fighter'];
		return $this;
	}

	#[ArrayShape(['fighter' => 'string'])]
	#[Pure] protected function getParameters(): array {
		return ['fighter' => $this->fighter];
	}

	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'fighter', 'string');
	}
}
