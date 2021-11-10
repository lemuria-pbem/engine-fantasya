<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Act\SeekMessage;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Unit;

/**
 * A seeking monster tries to spot a random outdoor player unit in the region.
 */
class Seek implements Act
{
	use ActTrait;
	use MessageTrait;

	protected ?Unit $enemy = null;

	public function Enemy(): ?Unit {
		return $this->enemy;
	}

	public function act(): Act {
		$enemies  = [];
		$calculus = new Calculus($this->unit);
		$region   = $this->unit->Region();
		foreach ($region->Residents() as $unit /* @var Unit $unit */) {
			if ($unit->Party()->Type() !== Party::PLAYER) {
				continue;
			}
			if ($unit->Construction() || $unit->Vessel()) {
				continue;
			}
			if ($calculus->canDiscover($unit)) {
				$enemies[] = $unit;
			}
		}

		if (!empty($enemies)) {
			$this->enemy = $enemies[array_rand($enemies)];
			$this->message(SeekMessage::class, $this->unit)->e($region);
		}
		return $this;
	}
}
