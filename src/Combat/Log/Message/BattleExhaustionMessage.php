<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Serializable;
use Lemuria\Validate;

class BattleExhaustionMessage extends AbstractMessage
{
	private const ROUNDS = 'rounds';

	protected array $simpleParameters = [self::ROUNDS];

	public function __construct(protected ?int $rounds = null) {
		parent::__construct();
	}

	public function getDebug(): string {
		return 'Battle ended in a draw due to exhaustion (' . $this->rounds. ' rounds without damage).';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->rounds = $data[self::ROUNDS];
		return $this;
	}

	protected function getParameters(): array {
		return [self::ROUNDS => $this->rounds];
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::ROUNDS, Validate::Int);
	}
}
