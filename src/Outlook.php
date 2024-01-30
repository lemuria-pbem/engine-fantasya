<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Effect\ContactEffect;
use Lemuria\Engine\Fantasya\Effect\FarsightEffect;
use Lemuria\Engine\Fantasya\Effect\HibernateEffect;
use Lemuria\Engine\Fantasya\Effect\TravelEffect;
use Lemuria\Engine\Fantasya\Factory\Model\TravelAtlas;
use Lemuria\Engine\Fantasya\Factory\Model\Visibility;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Building\Lighthouse;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Navigable;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\SortMode;

/**
 * Helper methods for report generation.
 */
final class Outlook
{
	use BuilderTrait;

	public function __construct(private readonly Census $census) {
	}

	public function Census(): Census {
		return $this->census;
	}

	/**
	 * Find units in a region that are not camouflaged.
	 *
	 * @noinspection DuplicatedCode
	 */
	public function getApparitions(Region $region): People {
		$perception = self::createTalent(Perception::class);
		$level      = PHP_INT_MIN;
		foreach ($this->census->getPeople($region) as $unit) {
			$calculus = new Calculus($unit);
			$level    = max($level, $calculus->knowledge($perception)->Level());
			$level    = max($level, $this->getFarsightPerception($region));
		}

		$units = new People();
		$party = $this->census->Party();
		$state = State::getInstance();
		$score = Lemuria::Score();
		foreach ($region->Residents() as $unit) {
			$calculus = new Calculus($unit);
			if ($calculus->isInvisible()) {
				continue;
			}
			if (!$unit->Construction() && !$unit->Vessel()) {
				$other = $unit->Party();
				if ($other === $party) {
					$units->add($unit);
				} elseif ($other->Type() === Type::Monster && $this->isHibernating($unit)) {
					continue;
				} elseif (!$unit->IsHiding() || $unit->IsGuarding()) {
					$units->add($unit);
				} elseif ($other->Diplomacy()->has(Relation::PERCEPTION, $this->census->Party())) {
					$units->add($unit);
				} else {
					$effect = new ContactEffect($state);
					$effect = $score->find($effect->setParty($party));
					if ($effect instanceof ContactEffect && $effect->From()->has($unit->Id())) {
						$units->add($unit);
					} elseif ($calculus->camouflage()->Level() <= $level) {
						$units->add($unit);
					}
				}
			}
		}
		return $units;
	}

	/**
	 * Find units in a region that are available for contacting.
	 *
	 * @noinspection DuplicatedCode
	 */
	public function getContacts(Region $region): People {
		$perception = self::createTalent(Perception::class);
		$level      = PHP_INT_MIN;
		foreach ($this->census->getPeople($region) as $unit) {
			$calculus = new Calculus($unit);
			$level    = max($level, $calculus->knowledge($perception)->Level());
		}

		$units = new People();
		$party = $this->census->Party();
		$state = State::getInstance();
		$score = Lemuria::Score();
		foreach ($region->Residents() as $unit) {
			$calculus = new Calculus($unit);
			if ($calculus->isInvisible()) {
				continue;
			}
			if ($unit->Construction()) {
				$units->add($unit);
			} elseif ($unit->Vessel()) {
				$units->add($unit);
			} else {
				$other = $unit->Party();
				if ($other === $party || !$unit->IsHiding() || $unit->IsGuarding()) {
					$units->add($unit);
				} elseif ($other->Diplomacy()->has(Relation::PERCEPTION, $this->census->Party())) {
					$units->add($unit);
				} else {
					$effect = new ContactEffect($state);
					$effect = $score->find($effect->setParty($party));
					if ($effect instanceof ContactEffect && $effect->From()->has($unit->Id())) {
						$units->add($unit);
					} else {
						if ($calculus->camouflage()->Level() <= $level) {
							$units->add($unit);
						}
					}
				}
			}
		}
		return $units;
	}

	/**
	 * Find units in a region that has been travelled.
	 */
	public function getTravelled(Region $region): People {
		$units = new People();
		$party = $this->census->Party();
		foreach ($region->Residents() as $unit) {
			if (!$unit->IsHiding() && !$unit->Construction() && !$unit->Vessel() && !$this->hasTravelled($unit)) {
				$other = $unit->Party();
				if ($other !== $party && $other->Type() === Type::Monster && $this->isHibernating($unit)) {
					continue;
				}
				$units->add($unit);
			}
		}
		return $units;
	}

	/**
	 * Get regions that are visible from a region.
	 */
	public function getPanorama(Region $region): TravelAtlas {
		$visible = new TravelAtlas($this->census->Party());
		$world   = Lemuria::World();
		$range   = $this->getVisibilityRange($region);
		if ($range > 0) {
			$hasLighthouse = true;
		} else {
			$range = 1;
			$hasLighthouse = false;
		}

		// Add direct neighbours and collect directions.
		$directions = [];
		foreach ($world->getNeighbours($region) as $direction => $neighbour) {
			if ($neighbour->Landscape() instanceof Navigable) {
				$directions[] = $direction;
				if ($hasLighthouse) {
					$visible->setVisibility($neighbour, Visibility::Lighthouse);
				}
			} else {
				$visible->setVisibility($neighbour, Visibility::Neighbour);
			}
			$visible->add($neighbour);
		}

		// Find paths to directions and add target regions.
		$distance = 1;
		while ($distance++ < $range) {
			$nextDirections = $directions;
			foreach ($nextDirections as $i => $direction) {
				$isWater = false;
				foreach ($world->getPath($region, $direction, $distance) as $way) {
					$neighbour = $way->last();
					if ($neighbour->Landscape() instanceof Navigable) {
						$isWater = true; // Filter out directions that have no water area as target.
					}
					if ($world->getDistance($region, $neighbour) === $distance) {
						$visible->add($neighbour);
						$visible->setVisibility($neighbour, Visibility::Lighthouse);
					}
				}
				if (!$isWater) {
					unset($directions[$i]);
				}
			}
		}

		return $visible->sort(SortMode::NorthToSouth);
	}

	/**
	 * Get the distance within a party can see neighbour regions.
	 *
	 * The normal distance is one and can be greater if a unit is in a lighthouse.
	 */
	protected function getVisibilityRange(Region $region): int {
		$range = 0;
		$party = $this->Census()->Party();
		foreach ($region->Estate() as $construction) {
			if ($construction->Building() instanceof Lighthouse) {
				$lRange = (int)floor(log10($construction->Size())) + 1;
				foreach ($construction->Inhabitants() as $unit) {
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

	private function getFarsightPerception(Region $region): int {
		$effect   = new FarsightEffect(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setRegion($region));
		return $existing instanceof FarsightEffect ? $existing->getPerception($this->Census()->Party()) : 0;
	}

	private function isHibernating(Unit $unit): bool {
		$effect = new HibernateEffect(State::getInstance());
		return Lemuria::Score()->find($effect->setUnit($unit)) instanceof HibernateEffect;
	}

	private function hasTravelled(Unit $unit): bool {
		$effect = new TravelEffect(State::getInstance());
		return Lemuria::Score()->find($effect->setUnit($unit)) instanceof TravelEffect;
	}
}
