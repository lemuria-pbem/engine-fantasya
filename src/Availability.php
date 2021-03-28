<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Effect\Unemployment;
use Lemuria\Engine\Fantasya\Event\Population;
use Lemuria\Lemuria;
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
	 */
	#[Pure] protected function calculateAvailability(Quantity $quantity): Quantity {
		$commodity = $quantity->Commodity();
		$count     = match ($commodity::class) {
			Peasant::class => $this->getUnemployedPeasants($quantity->Count()),
			default        => $quantity->Count()
		};
		return new Quantity($commodity, $count);
	}

	private function getUnemployedPeasants(int $totalPeasants): int {
		$effect       = new Unemployment(State::getInstance());
		/** @var Unemployment $unemployment */
		$unemployment = Lemuria::Score()->find($effect->setRegion($this->region));
		return $unemployment?->Peasants() ?? (int)ceil(Population::UNEMPLOYMENT / 100.0 * $totalPeasants);
	}
}
