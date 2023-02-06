<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Travel\Trip;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Travel\Conveyance;
use Lemuria\Engine\Fantasya\Travel\Movement;
use Lemuria\Engine\Fantasya\Travel\Trip;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Potion\GoliathWater;
use Lemuria\Model\Fantasya\Commodity\Potion\SevenLeagueTea;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Transport;

abstract class AbstractTrip implements Trip
{
	use BuilderTrait;

	protected final const MIN_SPEED = 1;

	protected Movement $movement;

	protected int $capacity = 0;

	protected int $knowledge;

	protected int $weight;

	protected Conveyance $conveyance;

	public function __construct(protected readonly Calculus $calculus, ?Conveyance $conveyance = null) {
		if ($conveyance) {
			$this->conveyance = $conveyance;
		}
		$this->calculateCapacity();
		$this->calculateKnowledge();
		$this->calculateWeight();
	}

	public function Capacity(): int {
		return $this->capacity;
	}

	public function Knowledge(): int {
		return $this->knowledge;
	}

	public function Movement(): Movement {
		return $this->movement;
	}

	public function Weight(): int {
		return $this->weight;
	}

	abstract protected function calculateCapacity(): void;

	abstract protected function calculateKnowledge(): void;

	protected function calculateWeight(): void {
		$this->weight = $this->calculus->Unit()->Weight();
	}

	protected function addCapacity(Quantity $quantity): void {
		/** @var Transport $transport */
		$transport = $quantity->Commodity();
		$count     = $quantity->Count();
		if ($count) {
			$this->capacity += $count * $transport->Payload();
		}
	}

	protected function removeWeightOf(string $commodity): void {
		$inventory = $this->calculus->Unit()->Inventory();
		if ($inventory->offsetExists($commodity)) {
			$quantity      = $inventory->offsetGet($commodity);
			$this->weight -= $quantity->Weight();
		}
	}

	/**
	 * @param array<string>
	 * @return array<int>
	 */
	protected function getSpeed(array $transports): int {
		$speed = [];
		foreach ($transports as $class) {
			$quantity = $this->conveyance->getQuantity($class);
			if ($quantity->Count()) {
				/** @var Transport $transport */
				$transport = $quantity->Commodity();
				$speed[]   = $transport->Speed();
			}
		}
		return empty($speed) ? self::MIN_SPEED : min($speed);
	}

	protected function getPayloadBoost(): int {
		$boostSize = $this->calculus->hasApplied(GoliathWater::class)?->Count() * GoliathWater::PERSONS;
		if ($boostSize > 0) {
			$payloadBoost = min($this->calculus->Unit()->Size(), $boostSize);
			return $payloadBoost * $this->getHorsePayload();
		}
		return 0;
	}

	protected function getUnitSpeed(): int {
		$unit      = $this->calculus->Unit();
		$size      = $unit->Size();
		$boostSize = $this->calculus->hasApplied(SevenLeagueTea::class)?->Count() * SevenLeagueTea::PERSONS;
		if ($boostSize >= $size) {
			return 2 * $unit->Race()->Speed();
		}
		return $unit->Race()->Speed();
	}

	private function getHorsePayload(): int {
		/** @var Horse $horse */
		$horse = self::createCommodity(Horse::class);
		return $horse->Payload();
	}
}
