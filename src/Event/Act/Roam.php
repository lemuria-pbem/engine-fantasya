<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Act\RoamHereMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Act\RoamMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Act\RoamStayMessage;
use Lemuria\Model\Fantasya\Region;

/**
 * A monster will roam according to its preferred landscapes with decreasing possibility.
 * If there are no matching landscapes around, it will stay in its current region.
 */
class Roam implements Act
{
	use ActTrait;
	use MessageTrait;

	public function act(): Roam {
		$region  = $this->unit->Region();
		$regions = $this->getPossibleRegions();
		if (empty($regions)) {
			$this->noPossibleRegion($region);
		} else {
			$regions = $this->chooseLandscape($regions);
			$target  = $regions[rand(0, count($regions) - 1)];
			if ($target === $region) {
				$this->message(RoamHereMessage::class, $this->unit)->e($region);
			} else {
				$this->moveTo($target);
				$this->message(RoamMessage::class, $this->unit)->e($region);
			}
		}
		return $this;
	}

	protected function noPossibleRegion(Region $region): void {
		$this->message(RoamStayMessage::class, $this->unit)->e($region);
	}
}
