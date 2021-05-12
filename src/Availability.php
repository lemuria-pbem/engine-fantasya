<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Effect\Unemployment;
use Lemuria\Engine\Fantasya\Event\Population;
use Lemuria\Engine\Fantasya\Factory\Model\Herb;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Herb as HerbInterface;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Resources;

class Availability
{
	use BuilderTrait;

	protected const HERBS_PER_REGION = 100;

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
				if ($offset instanceof Commodity) {
					$commodity = $offset;
				} else {
					$commodity = self::createCommodity($offset);
				}
				$quantity = new Quantity($commodity, 0);
			}
			$available = $this->calculateAvailability($quantity);
			$this->available->add($available);
		}
		return $available;
	}

	public function remove(Quantity $resource): void {
		$commodity = $resource->Commodity();
		$available = $this->getResource($commodity);
		$available->remove($resource);
		if ($commodity instanceof HerbInterface) {
			$herbage = $this->region->Herbage();
			if ($commodity === $herbage->Herb()) {
				$herbage->setOccurrence($available->Count() / self::HERBS_PER_REGION);
				return;
			}
		}
		$this->region->Resources()->remove($resource);
	}

	/**
	 * Calculate availability of each commodity individually.
	 */
	protected function calculateAvailability(Quantity $quantity): Quantity {
		$commodity = $quantity->Commodity();
		$count     = match ($commodity::class) {
			Peasant::class => $this->getUnemployedPeasants($quantity->Count()),
			Herb::class    => $this->getHerbCount(),
			default        => $this->getDefaultCount($quantity)
		};
		return new Quantity($commodity, $count);
	}

	private function getUnemployedPeasants(int $totalPeasants): int {
		$effect       = new Unemployment(State::getInstance());
		/** @var Unemployment $unemployment */
		$unemployment = Lemuria::Score()->find($effect->setRegion($this->region));
		return $unemployment?->Peasants() ?? (int)ceil(Population::UNEMPLOYMENT / 100.0 * $totalPeasants);
	}

	#[Pure] private function getHerbCount(?HerbInterface $herb = null):int {
		$herbage = $this->region->Herbage();
		if ($herbage && (!$herb || $herb === $herbage->Herb())) {
			return (int)round($herbage->Occurrence() * self::HERBS_PER_REGION);
		}
		return 0;
	}

	#[Pure] private function getDefaultCount(Quantity $quantity): int {
		$commodity = $quantity->Commodity();
		if ($commodity instanceof HerbInterface) {
			return $this->getHerbCount($commodity);
		}
		return $quantity->Count();
	}
}
