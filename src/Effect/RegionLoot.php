<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\RegionLootMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Animal;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Validate;

final class RegionLoot extends AbstractRegionEffect
{
	use MessageTrait;

	private const ROUNDS = 'rounds';

	private int $rounds = 10;

	private Resources $resources;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->resources = new Resources();
	}

	public function Resources(): Resources {
		return $this->resources;
	}

	public function Rounds(): int {
		return $this->rounds;
	}

	public function serialize(): array {
		$data               = parent::serialize();
		$data[self::ROUNDS] = $this->rounds;
		return $data;
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->rounds = $data[self::ROUNDS];
		return $this;
	}

	public function setRounds(int $rounds): RegionLoot {
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
		$region      = $this->Region();
		$environment = $region->Resources();
		$residents   = $region->Residents();
		$candidates  = new People();
		$removed     = new Resources();
		foreach ($this->resources as $quantity) {
			$commodity = $quantity->Commodity();
			$candidates->clear();
			foreach ($residents as $unit) {
				if ($unit->Size() > 0 && $unit->Party()->Loot()->wants($commodity)) {
					$candidates->add($unit);
				}
			}
			if ($candidates->isEmpty()) {
				if ($commodity instanceof Animal) {
					$removed->add($quantity);
					$environment->add(new Quantity($commodity, $quantity->Count()));
					Lemuria::Log()->debug($quantity . ' is added to the region resources.');
				}
			} else {
				$removed->add($quantity);
				$unit = $candidates->random();
				$unit->Inventory()->add(new Quantity($commodity, $quantity->Count()));
				$this->message(RegionLootMessage::class, $unit)->e($region)->i($quantity);
			}
		}
		foreach ($removed as $quantity) {
			$this->resources->remove($quantity);
		}

		if ($this->resources->isEmpty()) {
			Lemuria::Score()->remove($this);
		} elseif ($this->rounds-- <= 0) {
			Lemuria::Score()->remove($this);
			Lemuria::Log()->debug('The loot in region ' . $region . ' is disintegrated.');
		}
	}
}
