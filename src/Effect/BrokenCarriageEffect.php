<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Serializable;

final class BrokenCarriageEffect extends AbstractPartyEffect
{
	private ?Unit $unit = null;

	public function __construct(State $state) {
		parent::__construct($state, Action::BEFORE);
	}

	public function Unit(): ?Unit {
		return $this->unit;
	}

	#[ArrayShape(['class' => "string", 'id' => "int", 'unit' => "int"])]
	#[Pure] public function serialize(): array {
		$data = parent::serialize();
		$data['unit'] = $this->unit->serialize();
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->unit->unserialize($data['unit']);
		return $this;
	}

	public function setUnit(Unit $unit): BrokenCarriageEffect {
		$this->unit = $unit;
		return $this;
	}

	/**
	 * @param array (string=>mixed) &$data
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'unit', 'int');
	}

	protected function run(): void {
	}
}
