<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Effect\PotionInfluence;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Camel;
use Lemuria\Model\Fantasya\Commodity\Elephant;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Commodity\Wood;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Potion;
use Lemuria\Model\Fantasya\Region;

trait WorkplacesTrait
{
	use BuilderTrait;

	private Workplaces $workplaces;

	private function getAvailableWorkplaces(Region $region): int {
		return max(0, $region->Landscape()->Workplaces() - $this->getUsedWorkplaces($region));
	}

	private function getUsedWorkplaces(Region $region): int {
		$resources = $region->Resources();
		$trees     = $resources[self::createCommodity(Wood::class)]->Count();
		$horses    = $resources[self::createCommodity(Horse::class)]->Count();
		$camels    = $resources[self::createCommodity(Camel::class)]->Count();
		$elephants = $resources[self::createCommodity(Elephant::class)]->Count();
		return $this->workplaces->getUsed($horses, $camels, $elephants, $trees);
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
}
