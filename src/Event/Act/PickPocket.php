<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Event\Support;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Act\PicketPocketMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Act\PicketPocketOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Act\PicketPocketRevealedMessage;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

/**
 * A monster steals some silver from an enemy unit's pocket.
 */
class PickPocket implements Act
{
	use ActTrait;
	use BuilderTrait;
	use MessageTrait;

	protected const PICK = 10;

	protected const MAXIMUM = 0.1;

	protected Unit $enemy;

	public function act(): PickPocket {
		if ($this->enemy->Size() > 0) {
			$calculus = new Calculus($this->enemy);
			if ($calculus->canDiscover($this->unit)) {
				$this->message(PicketPocketRevealedMessage::class, $this->unit)->e($this->enemy);
			} else {
				$silver    = self::createCommodity(Silver::class);
				$wantPick  = $this->unit->Size() * self::PICK;
				$inventory = $this->enemy->Inventory();
				$pocket    = $inventory[$silver]->Count();
				$support   = $this->enemy->Size() * Support::SILVER;
				$excess    = max(0, $pocket - $support);
				$maxPick   = (int)floor(self::MAXIMUM * $excess);
				$pick      = min($wantPick, $maxPick);
				if ($pick > 0) {
					$inventory->remove(new Quantity($silver, $pick));
					$quantity = new Quantity($silver, $pick);
					$this->unit->Inventory()->add($quantity);
					if ($pick < $wantPick) {
						$this->message(PicketPocketOnlyMessage::class, $this->unit)->e($this->enemy)->i($quantity);
					} else {
						$this->message(PicketPocketMessage::class, $this->unit)->e($this->enemy)->i($quantity);
					}
				}
			}
		}
		return $this;
	}

	public function setEnemy(Unit $unit): PickPocket {
		$this->enemy = $unit;
		return $this;
	}
}
