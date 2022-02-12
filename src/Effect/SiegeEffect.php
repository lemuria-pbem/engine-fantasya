<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Serializable;

final class SiegeEffect extends AbstractConstructionEffect
{
	private int $perception = 0;

	private bool $isActive = true;

	public function __construct(State $state) {
		parent::__construct($state, Priority::AFTER);
	}

	public function IsActive(): bool {
		return $this->isActive;
	}

	public function Perception(): int {
		return $this->perception;
	}

	#[ArrayShape(['class' => 'string', 'id' => "int", 'perception' => 'int'])]
	#[Pure] public function serialize(): array {
		$data               = parent::serialize();
		$data['perception'] = $this->perception;
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->perception = $data['perception'];
		return $this;
	}

	public function reset(): SiegeEffect {
		$this->isActive   = false;
		$this->perception = 0;
		return $this;
	}

	public function renew(Unit $unit): SiegeEffect {
		$calculus         = new Calculus($unit);
		$this->isActive   = true;
		$this->perception = max($this->perception, $calculus->knowledge(Perception::class)->Level());
		return $this;
	}

	/**
	 * @param array (string=>mixed) &$data
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'perception', 'int');
	}

	protected function run(): void {
		if (!$this->isActive) {
			Lemuria::Score()->remove($this);
		}
	}
}
