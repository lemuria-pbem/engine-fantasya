<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Event\Support;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Act\PickPocketLeaveMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Act\PickPocketMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Act\PickPocketNothingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Act\PickPocketOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Act\PickPocketRevealedMessage;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\People;
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

	protected const TRIES = 3;

	protected const NEED = 1;

	protected const PICK = 10;

	protected const MAXIMUM = 0.1;

	protected People $enemy;

	public function act(): PickPocket {
		$tries      = (int)ceil($this->unit->Size() / self::TRIES);
		$triesLeft  = $tries;
		$isRevealed = false;
		$allPicks   = 0;

		while (!$this->enemy->isEmpty() && $triesLeft-- > 0) {
			/** @var Unit $enemy */
			$enemy = $this->enemy->random();
			$this->enemy->remove($enemy);
			if ($enemy->Construction()) {
				continue;
			}

			$calculus = new Calculus($enemy);
			if ($calculus->canDiscover($this->unit)) {
				$isRevealed = true;
				$this->message(PickPocketRevealedMessage::class, $this->unit)->e($enemy);
			} else {
				$silver    = self::createCommodity(Silver::class);
				$wantPick  = $this->unit->Size() * self::PICK;
				$inventory = $enemy->Inventory();
				$pocket    = $inventory[$silver]->Count();
				$support   = $enemy->Size() * Support::SILVER;
				$excess    = max(0, $pocket - $support);
				$maxPick   = (int)floor(self::MAXIMUM * $excess);
				$pick      = min($wantPick, $maxPick);
				if ($pick > 0) {
					$allPicks += $pick;
					$inventory->remove(new Quantity($silver, $pick));
					$quantity = new Quantity($silver, $pick);
					$this->unit->Inventory()->add($quantity);
					if ($pick < $wantPick) {
						$this->message(PickPocketOnlyMessage::class, $this->unit)->e($enemy)->i($quantity);
					} else {
						$this->message(PickPocketMessage::class, $this->unit)->e($enemy)->i($quantity);
					}
				} else {
					$this->message(PickPocketNothingMessage::class, $this->unit)->e($enemy);
				}
			}
		}

		if ($isRevealed || $allPicks < $tries * self::NEED) {
			$this->message(PickPocketLeaveMessage::class, $this->unit);
			$this->createRoamEffect();
		}
		return $this;
	}

	public function setEnemy(People $enemy): PickPocket {
		$this->enemy = $enemy;
		return $this;
	}
}
