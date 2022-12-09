<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Serializable;
use Lemuria\Validate;

class GazeOfTheBasiliskMessage extends AbstractMessage
{
	private const ATTACKER = 'attacker';

	protected array $simpleParameters = [self::ATTACKER];

	public function __construct(protected ?string $attacker = null) {
	}

	public function getDebug(): string {
		return $this->attacker . ' is petrified by Gaze of the Basilisk.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->attacker = $data[self::ATTACKER];
		return $this;
	}

	protected function getParameters(): array {
		return [self::ATTACKER => $this->attacker];
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::ATTACKER, Validate::String);
	}
}
