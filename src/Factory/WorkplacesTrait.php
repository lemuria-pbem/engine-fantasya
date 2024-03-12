<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Effect\PotionInfluence;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Building;
use Lemuria\Model\Fantasya\Building\AbstractFarm;
use Lemuria\Model\Fantasya\Commodity\Camel;
use Lemuria\Model\Fantasya\Commodity\Elephant;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Commodity\Pegasus;
use Lemuria\Model\Fantasya\Commodity\Wood;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Potion;
use Lemuria\Model\Fantasya\Region;

trait WorkplacesTrait
{
	use BuilderTrait;
	use SiegeTrait;

	private Workplaces $workplaces;

	private function getPlaceForTrees(Region $region): int {
		$workplaces = $region->Landscape()->Workplaces();
		$used       = $this->getUsedWorkplaces($region);
		return max(0, $workplaces - $used);
	}

	private function getAvailableWorkplaces(Region $region): int {
		$workplaces = $region->Landscape()->Workplaces();
		$additional = $this->getAdditionalWorkplaces($region);
		$used       = $this->getUsedWorkplaces($region);
		return max(0, $workplaces + $additional - $used);
	}

	private function getAdditionalWorkplaces(Region $region): int {
		$additional = 0;
		$workplaces = $region->Landscape()->Workplaces();
		$trees      = $region->Resources()[Wood::class]->Count();
		$farms      = $this->getAllFarms($region);
		foreach ($farms as $class => $sizes) {
			$building   = self::createBuilding($class);
			$count      = count($sizes);
			$maxSize    = max($sizes);
			$additional = max($additional, $this->workplaces->getAdditional($building, $count, $maxSize, $workplaces, $trees));
		}
		return $additional;
	}

	private function getUsedWorkplaces(Region $region): int {
		$resources = $region->Resources();
		$trees     = $resources[self::createCommodity(Wood::class)]->Count();
		$horses    = $resources[self::createCommodity(Horse::class)]->Count();
		$pegasi    = $resources[self::createCommodity(Pegasus::class)]->Count();
		$camels    = $resources[self::createCommodity(Camel::class)]->Count();
		$elephants = $resources[self::createCommodity(Elephant::class)]->Count();
		return $this->workplaces->getUsed($horses, $pegasi, $camels, $elephants, $trees);
	}

	private function getCultivatedWorkplaces(Region $region): int {
		$resources = $region->Resources();
		return $resources[self::createCommodity(Peasant::class)]->Count();
	}

	private function hasApplied(Potion|string $potion, Region $region): int {
		if (is_string($potion)) {
			$potion = self::createCommodity($potion);
		}
		$effect = new PotionInfluence(State::getInstance());
		/** @var PotionInfluence $existing */
		$existing = Lemuria::Score()->find($effect->setRegion($region));
		return $existing?->hasPotion($potion) ? $existing->getCount($potion) : 0;
	}

	private function checkForRegionBuilding(Region $region, Building $building): bool {
		foreach ($region->Estate() as $construction) {
			if ($construction->Building() === $building) {
				$size = $construction->Size();
				if ($size >= $building->UsefulSize() && !$this->isSieged($construction)) {
					$owner = $construction->Inhabitants()->Owner();
					if ($owner && $this->context->getCalculus($owner)->isInMaintainedConstruction()) {
						return true;
					}
				}
			}
		}
		return false;
	}

	private function getAllFarms(Region $region): array {
		$farms = [];
		foreach ($region->Estate() as $construction) {
			$building = $construction->Building();
			if ($building instanceof AbstractFarm) {
				$size = $construction->Size();
				if ($size >= $building->UsefulSize() && !$this->isSieged($construction)) {
					$owner = $construction->Inhabitants()->Owner();
					if ($owner && $this->context->getCalculus($owner)->isInMaintainedConstruction()) {
						$farms[$building::class][] = $size;
					}
				}
			}
		}
		return $farms;
	}
}
