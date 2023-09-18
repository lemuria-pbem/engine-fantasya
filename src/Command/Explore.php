<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\DefaultActivityTrait;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Message\Unit\ExploreExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ExploreMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ExploreNothingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ExploreSiegeMessage;
use Lemuria\Model\Fantasya\Composition\HerbAlmanac;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Herbage;
use Lemuria\Model\Fantasya\Talent\Herballore;

/**
 * Explore the region to find herbs.
 *
 * - ERFORSCHEN
 * - ERFORSCHEN Kraut|Kräuter|Kraeuter
 * - FORSCHEN
 * - FORSCHEN Kraut|Kräuter|Kraeuter
 */
final class Explore extends UnitCommand implements Activity
{
	use BuilderTrait;
	use DefaultActivityTrait;
	use SiegeTrait;

	private bool $hasEnoughKnowledge = false;

	public static function occurrence(Herbage $herbage): string {
		$occurrence = $herbage->Occurrence();
		return match (true) {
			$occurrence <= 0.2 => 'tiny',
			$occurrence <= 0.4 => 'small',
			$occurrence <= 0.6 => 'average',
			$occurrence <= 0.8 => 'big',
			$occurrence <= 1.0 => 'huge'
		};
	}

	/**
	 * Allow writing of explored herbage.
	 */
	public function allows(Activity $activity): bool {
		if ($this->hasEnoughKnowledge) {
			return $activity instanceof Write && $activity->Composition() instanceof HerbAlmanac;
		}
		return true;
	}

	protected function run(): void {
		$n = $this->phrase->count();
		if ($n > 1) {
			throw new UnknownCommandException($this);
		}
		if ($n === 1) {
			switch (mb_strtolower($this->phrase->getParameter())) {
				case 'kraut' :
				case 'kräuter' :
				case 'kraeuter' :
					break;
				default :
					throw new UnknownCommandException($this);
			}
		}

		$knowledge = $this->calculus()->knowledge(Herballore::class)->Level();
		if ($knowledge < Herballore::EXPLORE_LEVEL) {
			$this->message(ExploreExperienceMessage::class);
			return;
		}

		if ($this->canEnterOrLeave($this->unit)) {
			$region  = $this->unit->Region();
			$herbage = $region->Herbage();
			$this->unit->Party()->HerbalBook()->record($region, $herbage);
			if ($herbage && !$this->context->getTurnOptions()->IsSimulation()) {
				$herb       = $herbage->Herb();
				$occurrence = self::occurrence($herbage);
				$this->message(ExploreMessage::class)->e($region)->s($herb)->p($occurrence);
			} else {
				$this->message(ExploreNothingMessage::class)->e($region);
			}
			$this->hasEnoughKnowledge = true;
		} else {
			$this->message(ExploreSiegeMessage::class);
		}
	}
}
