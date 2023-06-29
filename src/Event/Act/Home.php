<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use Lemuria\Engine\Fantasya\Message\Unit\Act\HomeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Act\HomeNowhereMessage;
use Lemuria\Model\Fantasya\Region;

/**
 * A homing monster will always stay in its preferred landscape.
 * If it is in an area of connected regions of the same landscape, it will roam within these regions.
 * Otherwise, it will randomly roam until it enters a region of its preferred landscape.
 */
class Home extends Roam
{
	protected function noPossibleRegion(Region $region): void {
		/** @var Region $target */
		$target = $this->chooseRandomNeighbour();
		if ($target) {
			$this->moveTo($target);
			$this->message(HomeMessage::class, $this->unit)->e($target);
		} else {
			$this->message(HomeNowhereMessage::class, $this->unit);
		}
	}
}
