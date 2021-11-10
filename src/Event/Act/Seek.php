<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Unit;

/**
 * A seeking monster will roam until it has spotted outdoor player units in the region.
 */
class Seek extends Roam
{
	protected bool $mayStayHere = false;

	public function act(): Roam {
		$calculus = new Calculus($this->unit);
		foreach ($this->unit->Region()->Residents() as $unit /* @var Unit $unit */) {
			if ($unit->Party()->Type() === Party::PLAYER) {
				continue;
			}
			if ($unit->Construction() || $unit->Vessel()) {
				continue;
			}
			if ($calculus->canDiscover($unit)) {
				//TODO found someone
				return $this;
			}
		}
		return parent::act();
	}
}
