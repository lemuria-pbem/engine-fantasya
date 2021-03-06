<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use JetBrains\PhpStorm\Pure;

use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Resources;

class Availability
{
	use BuilderTrait;

	protected Resources $available;

	#[Pure] public function __construct(protected Region $region) {
		$this->available = new Resources();
	}

	#[Pure] public function Region(): Region {
		return $this->region;
	}

	public function getResource(mixed $offset): Quantity {
		if (isset($this->available[$offset])) {
			$available = $this->available[$offset];
		} else {
			$resources = $this->region->Resources();
			if (isset($resources[$offset])) {
				$quantity = $resources[$offset];
			} else {
				$quantity = new Quantity(self::createCommodity($offset), 0);
			}
			$available = $this->calculateAvailability($quantity);
			$this->available->add($available);
		}
		return $available;
	}

	public function remove(Quantity $resource): void {
		$this->getResource($resource->Commodity())->remove($resource);
		$this->region->Resources()->remove($resource);
	}

	/**
	 * Calculate availability of each commodity individually.
	 *
	 * TODO: Improve calculation.
	 */
	#[Pure] protected function calculateAvailability(Quantity $quantity): Quantity {
		$commodity = $quantity->Commodity();
		$factor    = match ($commodity::class) {
			Peasant::class => rand(30, 60) / 1000,
			default        => 1.0
		};
		$count = (int)floor($factor * $quantity->Count());
		return new Quantity($commodity, $count);
	}
}
