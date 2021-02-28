<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Factory;

use Lemuria\Model\Lemuria\Commodity\Camel;
use Lemuria\Model\Lemuria\Commodity\Elephant;
use Lemuria\Model\Lemuria\Commodity\Horse;
use Lemuria\Model\Lemuria\Commodity\Tree;
use Lemuria\Model\Lemuria\Factory\BuilderTrait;
use Lemuria\Model\Lemuria\Region;

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
}
