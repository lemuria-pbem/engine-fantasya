<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\Behaviour;
use Lemuria\Engine\Fantasya\Message\Unit\Act\PreyMessage;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Unit;

/**
 * A hunting monster watches for the smallest unit in the region to attack.
 */
class Prey extends Seek
{
	public function __construct(Behaviour $behaviour) {
		parent::__construct($behaviour);
	}

	public function act(): Act {
		$calculus = new Calculus($this->unit);
		$region   = $this->unit->Region();
		$smallest = PHP_INT_MAX;
		$prey     = null;
		foreach ($region->Residents() as $unit /* @var Unit $unit */) {
			if ($unit->Party()->Type() === Type::MONSTER) {
				continue;
			}
			if ($unit->Construction() || $unit->Vessel()) {
				continue;
			}
			$size = $unit->Size();
			if ($size > 0 && $size < $smallest && $calculus->canDiscover($unit)) {
				$prey     = $unit;
				$smallest = $size;
			}
		}
		if ($smallest < $this->unit->Size()) {
			$this->enemy->add($prey);
			$this->message(PreyMessage::class, $this->unit)->e($region)->e($prey, PreyMessage::PREY);
		}
		return $this;
	}
}
