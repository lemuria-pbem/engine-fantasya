<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Validate;

final class DeceasedPeasants extends AbstractRegionEffect
{
	private const string PEASANTS = 'peasants';

	protected ?bool $isReassign = null;

	private int $peasants = 0;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	public function Peasants(): int {
		return $this->peasants;
	}

	public function serialize(): array {
		$data                 = parent::serialize();
		$data[self::PEASANTS] = $this->peasants;
		return $data;
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->peasants = $data[self::PEASANTS];
		return $this;
	}

	public function setPeasants(int $peasants): static {
		$this->peasants = $peasants;
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::PEASANTS, Validate::Int);
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
