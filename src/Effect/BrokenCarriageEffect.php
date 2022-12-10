<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Serializable;
use Lemuria\Validate;

final class BrokenCarriageEffect extends AbstractPartyEffect
{
	private const UNIT = 'unit';

	private ?Unit $unit = null;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	public function Unit(): ?Unit {
		return $this->unit;
	}

	public function serialize(): array {
		$data             = parent::serialize();
		$data[self::UNIT] = $this->unit->Id()->Id();
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->unit = Unit::get(new Id($data[self::UNIT]));
		return $this;
	}

	public function setUnit(Unit $unit): BrokenCarriageEffect {
		$this->unit = $unit;
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::UNIT, Validate::Int);
	}

	protected function run(): void {
	}
}
