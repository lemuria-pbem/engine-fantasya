<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Serializable;

abstract class AbstractFighterMessage extends AbstractMessage
{
	protected array $simpleParameters = ['fighter'];

	public function __construct(protected ?string $fighter = null) {
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->fighter = $data['fighter'];
		return $this;
	}

	protected function getParameters(): array {
		return ['fighter' => $this->fighter];
	}

	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'fighter', 'string');
	}
}
