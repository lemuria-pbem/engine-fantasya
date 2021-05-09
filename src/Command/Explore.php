<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\OneActivityTrait;
use Lemuria\Engine\Fantasya\Message\Unit\ExploreExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ExploreMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ExploreNothingMessage;
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
	use OneActivityTrait;

	private const LEVEL = 3;

	protected function run(): void {
		$n = $this->phrase->count();
		if ($n > 1) {
			throw new UnknownCommandException($this);
		}
		if ($n === 1) {
			switch (strtolower($this->phrase->getParameter())) {
				case 'kraut' :
				case 'kräuter' :
				case 'kraeuter' :
					break;
				default :
					throw new UnknownCommandException($this);
			}
		}

		$knowledge = $this->calculus()->knowledge(Herballore::class)->Level();
		if ($knowledge < self::LEVEL) {
			$this->message(ExploreExperienceMessage::class);
			return;
		}

		$region  = $this->unit->Region();
		$herbage = $region->Herbage();
		$this->unit->Party()->HerbalBook()->record($region, $herbage);
		if ($herbage) {
			$herb       = $herbage->Herb();
			$occurrence = $this->occurrence($herbage);
			$this->message(ExploreMessage::class)->e($region)->s($herb)->p($occurrence);
		} else {
			$this->message(ExploreNothingMessage::class)->e($region);
		}
	}

	private function occurrence(Herbage $herbage): string {
		$occurrence = $herbage->Occurrence();
		return match (true) {
			$occurrence <= 0.2 => 'tiny',
			$occurrence <= 0.4 => 'small',
			$occurrence <= 0.6 => 'average',
			$occurrence <= 0.8 => 'big',
			$occurrence <= 1.0 => 'huge'
		};
	}
}
