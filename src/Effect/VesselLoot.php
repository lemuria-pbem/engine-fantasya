<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\VesselLootMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Serializable;

final class VesselLoot extends AbstractVesselEffect
{
	use MessageTrait;

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
		$data['rounds'] = $this->rounds;
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->rounds = $data['rounds'];
		return $this;
	}

	public function setRounds(int $rounds): VesselLoot {
		$this->rounds = $rounds;
		return $this;
	}

	/**
	 * @param array (string=>mixed) &$data
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'rounds', 'int');
	}

	protected function run(): void {
		$vessel    = $this->Vessel();
		$captain   = $vessel->Passengers()->Owner();
		$inventory = $captain?->Inventory();
		if ($inventory) {
			foreach ($this->resources as $quantity /* @var Quantity $quantity */) {
				$inventory->add($quantity);
				$this->message(VesselLootMessage::class, $captain)->e($vessel)->i($quantity);
			}
			Lemuria::Score()->remove($this);
		}
		if ($this->rounds-- <= 0) {
			Lemuria::Score()->remove($this);
			Lemuria::Log()->debug('The loot on vessel ' . $vessel . ' is disintegrated.');
		}
	}
}
