<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Effect\ContactEffect;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Building\Lighthouse;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Landscape\Ocean;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\World\Atlas;

/**
 * Helper methods for report generation.
 */
final class Outlook
{
	use BuilderTrait;

	public function __construct(private Census $census) {
	}

	public function Census(): Census {
		return $this->census;
	}

	/**
	 * Find units in a region that are not camouflaged.
	 */
	public function Apparitions(Region $region): People {
		$perception = self::createTalent(Perception::class);
		$level      = PHP_INT_MIN;
		foreach ($this->census->getPeople($region) as $unit /* @var Unit $unit */) {
			$calculus = new Calculus($unit);
			$level    = max($level, $calculus->knowledge($perception)->Level());
		}

		$units      = new People();
		$party      = $this->census->Party();
		$camouflage = self::createTalent(Camouflage::class);
		foreach ($region->Residents() as $unit /* @var Unit $unit */) {
			if (!$unit->Construction() && !$unit->Vessel()) {
				if ($unit->Party() === $party || !$unit->IsHiding() || $unit->IsGuarding()) {
					$units->add($unit);
				} elseif ($unit->Party()->Diplomacy()->has(Relation::PERCEPTION, $this->census->Party())) {
					$units->add($unit);
				} else {
					$effect = new ContactEffect(State::getInstance());
					$effect = Lemuria::Score()->find($effect->setParty($party));
					if ($effect instanceof ContactEffect && $effect->From()->has($unit->Id())) {
						$units->add($unit);
					} else {
						$calculus = new Calculus($unit);
						if ($calculus->knowledge($camouflage)->Level() <= $level) {
							$units->add($unit);
						}
					}
				}
			}
		}
		return $units;
	}

	/**
	 * Get regions that are visible from a region.
	 */
	public function Panorama(Region $region): Atlas {
		$visible = new Atlas();
		$world   = Lemuria::World();
		$range   = $this->getVisibilityRange($region);

		// Add direct neighbours and collect directions.
		$directions = [];
		$neighbours = $world->getNeighbours($region)->getAll();
		foreach ($neighbours as $direction => $neighbour /* @var Region $neighbour */) {
			if ($neighbour->Landscape() instanceof Ocean) {
				$directions[] = $direction;
			}
			$visible->add($neighbour);
		}

		// Find paths to directions and add target regions.
		$distance = 1;
		while ($distance++ < $range) {
			$nextDirections = $directions;
			foreach ($nextDirections as $direction) {
				$isOcean = false;
				foreach ($world->getPath($region, $direction, $distance) as $way) {
					$neighbour = array_pop($way);
					if ($neighbour->Landscape() instanceof Ocean) {
						$isOcean = true; // Filter out directions that have no ocean as target.
					}
					$visible->add($neighbour);
				}
				if (!$isOcean) {
					unset($directions[$direction]);
				}
			}
		}

		return $visible->sort(Atlas::NORTH_TO_SOUTH);
	}

	/**
	 * Get the distance within a party can see neighbour regions.
	 *
	 * The normal distance is one and can be greater if a unit is in a lighthouse.
	 */
	protected function getVisibilityRange(Region $region): int {
		$range = 1;
		$party = $this->Census()->Party();
		foreach ($region->Estate() as $construction /* @var Construction $construction */) {
			if ($construction->Building() instanceof Lighthouse) {
				$lRange = (int)floor(log10($construction->Size())) + 1;
				foreach ($construction->Inhabitants() as $unit /* @var Unit $unit */) {
					if ($unit->Party() === $party) {
						$calculus   = new Calculus($unit);
						$perception = $calculus->knowledge(Perception::class)->Level();
						$pRange     = (int)floor($perception / 2);
						$range      = max($range, min($lRange, $pRange));
					}
				}
			}
		}
		return $range;
	}
}
