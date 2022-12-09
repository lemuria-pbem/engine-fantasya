<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionLootMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Serializable;
use Lemuria\Validate;

final class ConstructionLoot extends AbstractConstructionEffect
{
	use MessageTrait;

	private const ROUNDS = 'rounds';

	private int $rounds = PHP_INT_MAX;

	private Resources $resources;

	public function __construct(State $state) {
		parent::__construct($state, Priority::AFTER);
		$this->resources = new Resources();
	}

	public function Resources(): Resources {
		return $this->resources;
	}

	public function Rounds(): int {
		return $this->rounds;
	}

	public function serialize(): array {
		$data           = parent::serialize();
		$data[self::ROUNDS] = $this->rounds;
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->rounds = $data[self::ROUNDS];
		return $this;
	}

	public function setRounds(int $rounds): ConstructionLoot {
		$this->rounds = $rounds;
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
		$construction = $this->Construction();
		$owner        = $construction->Inhabitants()->Owner();
		$inventory    = $owner?->Inventory();
		if ($inventory) {
			foreach ($this->resources as $quantity /* @var Quantity $quantity */) {
				$inventory->add($quantity);
				$this->message(ConstructionLootMessage::class, $owner)->e($construction)->i($quantity);
			}
			Lemuria::Score()->remove($this);
		}
		if ($this->rounds-- <= 0) {
			Lemuria::Score()->remove($this);
			Lemuria::Log()->debug('The loot in construction ' . $construction . ' is disintegrated.');
		}
	}
}
