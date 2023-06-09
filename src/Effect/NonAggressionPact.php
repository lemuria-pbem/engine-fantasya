<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Message\Party\NonAggressionPactEndsMessage;
use Lemuria\Engine\Fantasya\Message\Party\NonAggressionPactLastMessage;
use Lemuria\Engine\Fantasya\Message\Party\NonAggressionPactMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Serializable;
use Lemuria\Validate;

final class NonAggressionPact extends AbstractPartyEffect
{
	private const ROUNDS = 'rounds';

	private int $rounds = 24;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	public function Rounds(): int {
		return $this->rounds;
	}

	public function serialize(): array {
		$data               = parent::serialize();
		$data[self::ROUNDS] = $this->rounds;
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->rounds = $data[self::ROUNDS];
		return $this;
	}

	public function setRounds(int $rounds): NonAggressionPact {
		$this->rounds = max(0, $rounds);
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::ROUNDS, Validate::Int);
	}

	protected function run(): void {
		if ($this->rounds > 1) {
			if ($this->rounds > 2) {
				$this->message(NonAggressionPactMessage::class, $this->Party())->p($this->rounds--);
			} else {
				$this->message(NonAggressionPactLastMessage::class, $this->Party());
				$this->rounds--;
			}
		} else {
			$this->message(NonAggressionPactEndsMessage::class, $this->Party());
			Lemuria::Score()->remove($this);
		}
	}
}
