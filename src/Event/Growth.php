<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Event;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Action;
use Lemuria\Engine\Lemuria\Factory\Model\Season;
use Lemuria\Engine\Lemuria\Factory\Workplaces;
use Lemuria\Engine\Lemuria\Message\Region\GrowthMessage;
use Lemuria\Engine\Lemuria\State;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Lemuria\Commodity;
use Lemuria\Model\Lemuria\Commodity\Tree;
use Lemuria\Model\Lemuria\Factory\BuilderTrait;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Region;

/**
 * Peasants work for their living and increase their silver reserve.
 */
final class Growth extends AbstractEvent
{
	use BuilderTrait;

	public const RATE = 0.02;

	private const NEIGHBOUR = 0.003;

	private const RANDOM = 0.3;

	private bool $isSeason = false;

	private Commodity $tree;

	#[Pure] public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
		$this->tree = self::createCommodity(Tree::class);
	}

	protected function initialize(): void {
		$this->isSeason = Lemuria::Calendar()->Season() === Season::SPRING;
	}

	protected function run(): void {
		if (!$this->isSeason) {
			Lemuria::Log()->debug('We have no tree growth season.');
			return;
		}

		foreach (Lemuria::Catalog()->getAll(Catalog::LOCATIONS) as $region /* @var Region $region */) {
			$landscape = $region->Landscape();
			$place     = (int)round($landscape->Workplaces() / Workplaces::TREE);
			if ($place < 0) {
				return;
			}

			$resources = $region->Resources();
			$trees     = $resources[$this->tree]->Count();
			$rate      = $trees < $place ? self::RATE : self::RATE / 10;
			$growth    = (int)ceil($rate * $trees);
			$random    = $growth >= 30 ? 5 : 1;
			$growth    = max(0, rand($growth - $random, $growth + $random));

			if ($trees < 0.01 * $place) {
				$neighbourTrees = $this->countNeighbourTrees($region);
				$moreGrowth     = (int)round(self::NEIGHBOUR * $neighbourTrees);
				if ($moreGrowth <= 0) {
					$random = (int)(self::RANDOM * 100);
					if (rand(0, 100) < $random) {
						$moreGrowth++;
					}
				}
				$growth += $moreGrowth;
			}

			$newTrees = new Quantity($this->tree, $growth);
			$resources->add($newTrees);
			$this->message(GrowthMessage::class, $region)->i($newTrees);
		}
	}

	private function countNeighbourTrees(Region $region): int {
		$trees = 0;
		foreach (Lemuria::World()->getNeighbours($region)->getAll() as $neighbour /* @var Region $neighbour */) {
			$trees += $neighbour->Resources()[$this->tree]->Count();
		}
		return $trees;
	}
}
