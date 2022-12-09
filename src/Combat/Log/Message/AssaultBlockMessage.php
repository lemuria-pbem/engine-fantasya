<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Serializable;
use Lemuria\Validate;

class AssaultBlockMessage extends AbstractMessage
{
	private const ATTACKER = 'attacker';

	private const DEFENDER = 'defender';

	protected array $simpleParameters = [self::ATTACKER, self::DEFENDER];

	public function __construct(protected ?string $attacker = null, protected ?string $defender = null) {
	}

	public function getDebug(): string {
		return $this->defender . ' blocks attack from ' . $this->attacker . '.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->attacker = $data[self::ATTACKER];
		$this->defender = $data[self::DEFENDER];
		return $this;
	}

	protected function getParameters(): array {
		return [self::ATTACKER => $this->attacker, self::DEFENDER => $this->defender];
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::ATTACKER, Validate::String);
		$this->validate($data, self::DEFENDER, Validate::String);
	}
}
