<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Effect\ContactEffect;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Unit;

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
				if ($unit->Party() === $party || !$unit->IsHiding()) {
					$units->add($unit);
				} elseif ($unit->Party()->Diplomacy()->has(Relation::PERCEPTION, $this->census->Party())) {
					$units->add($unit);
				} else {
					$effect = new ContactEffect(State::getInstance());
					$effect = Lemuria::Score()->find($effect->setParty($party));
					if ($effect && $effect->From()->has($unit->Id())) {
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
	public function Panorama(Region $region): array {
		// TODO: Extend when effects are implemented.
		return Lemuria::World()->getNeighbours($region)->getAll();
	}
}
