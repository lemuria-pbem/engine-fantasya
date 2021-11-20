<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Event\Behaviour;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Act\SeekMessage;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Unit;

/**
 * A seeking monster tries to spot a random outdoor player unit in the region.
 */
class Seek implements Act
{
	use ActTrait;
	use MessageTrait;

	protected People $enemy;

	public function __construct(Behaviour $behaviour) {
		$this->unit  = $behaviour->Unit();
		$this->enemy = new People();
	}

	public function Enemy(): People {
		return $this->enemy;
	}

	public function act(): Act {
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
				$this->enemy->add($unit);
			}
		}
		if ($this->enemy->count() > 0) {
			$this->message(SeekMessage::class, $this->unit)->e($region);
		}
		return $this;
	}
}
