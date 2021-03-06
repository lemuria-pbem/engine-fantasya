<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Model\Fantasya\Commodity\Camel;
use Lemuria\Model\Fantasya\Commodity\Elephant;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Commodity\Tree;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
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
		$trees     = $resources[self::createCommodity(Tree::class)]->Count();
		$horses    = $resources[self::createCommodity(Horse::class)]->Count();
		$camels    = $resources[self::createCommodity(Camel::class)]->Count();
		$elephants = $resources[self::createCommodity(Elephant::class)]->Count();
		return $this->workplaces->getUsed($horses, $camels, $elephants, $trees);
	}

	private function getCultivatedWorkplaces(Region $region): int {
		$resources = $region->Resources();
		$peasants  = $resources[self::createCommodity(Peasant::class)]->Count();
		return $peasants;
	}
}
