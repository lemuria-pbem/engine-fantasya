<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use function Lemuria\randKey;
use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Act\RoamHereMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Act\RoamMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Act\RoamStayMessage;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\World\Direction;

/**
 * A monster will roam according to its preferred landscapes with decreasing possibility.
 * If there are no matching landscapes around, it will stay in its current region.
 */
class Roam implements Act
{
	use ActTrait;
	use MessageTrait;

	protected bool $leave = false;

	protected bool $hasMoved = false;

	public function HasMoved(): bool {
		return $this->hasMoved;
	}

	public function act(): static {
		$region  = $this->unit->Region();
		$regions = $this->getPossibleRegions(!$this->leave);
		if (empty($regions)) {
			$this->noPossibleRegion($region);
		} else {
			$regions   = $this->chooseLandscape($regions);
			$key       = randKey($regions);
			$direction = Direction::from($key);
			$target    = $regions[$key];
			if ($direction === Direction::None) {
				$this->message(RoamHereMessage::class, $this->unit)->e($region);
			} else {
				$this->moveTo($direction, $target);
				$this->hasMoved = true;
				$this->message(RoamMessage::class, $this->unit)->e($target);
			}
		}
		return $this;
	}

	public function setLeave(bool $leave): Roam {
		$this->leave = $leave;
		return $this;
	}

	protected function noPossibleRegion(Region $region): void {
		$this->message(RoamStayMessage::class, $this->unit)->e($region);
	}
}
