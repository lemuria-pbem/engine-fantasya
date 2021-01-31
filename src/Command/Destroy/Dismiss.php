<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Destroy;

use Lemuria\Engine\Lemuria\Allocation;
use Lemuria\Engine\Lemuria\Command\GiftTrait;
use Lemuria\Engine\Lemuria\Command\UnitCommand;
use Lemuria\Engine\Lemuria\Exception\UnknownCommandException;
use Lemuria\Engine\Lemuria\Factory\Model\Everything;
use Lemuria\Engine\Lemuria\Message\Unit\DismissAllMessage;
use Lemuria\Engine\Lemuria\Message\Unit\DismissEverythingMessage;
use Lemuria\Engine\Lemuria\Message\Unit\DIsmissMessage;
use Lemuria\Engine\Lemuria\Message\Unit\DismissNoneMessage;
use Lemuria\Engine\Lemuria\Message\Unit\DismissNothingMessage;
use Lemuria\Engine\Lemuria\Message\Unit\DismissOnlyMessage;
use Lemuria\Engine\Lemuria\Message\Unit\LoseAllMessage;
use Lemuria\Engine\Lemuria\Message\Unit\LoseMessage;
use Lemuria\Engine\Lemuria\Message\Unit\LoseNothingMessage;
use Lemuria\Engine\Lemuria\Message\Unit\LoseOnlyMessage;
use Lemuria\Model\Lemuria\Commodity\Peasant;
use Lemuria\Model\Lemuria\Quantity;

/**
 * Implementation of command ENTLASSEN.
 *
 * The command transfers a unit's persons and donates its commodities to the peasant population of the region.
 * Commodities that have no value for the peasants are lost.
 *
 * - ENTLASSEN
 * - ENTLASSEN Alles
 * - ENTLASSEN <commodity>
 * - ENTLASSEN Person|Personen
 * - ENTLASSEN Alles <commodity>
 * - ENTLASSEN <amount> <commodity>
 * - ENTLASSEN <amount> Person|Personen
 *
 * - GIB Bauern
 * - GIB Bauern Alles
 * - GIB Bauern <commodity>
 * - GIB Bauern Person|Personen
 * - GIB Bauern Alles <commodity>
 * - GIB Bauern <amount> <commodity>
 * - GIB Bauern <amount> Person|Personen
 */
final class Dismiss extends UnitCommand
{
	use GiftTrait;

	protected function run(): void {
		$p = 1;
		$count = $this->phrase->getParameter($p++);
		if ($this->phrase->getVerb() === 'GIB') {
			if (strtolower($count) !== 'bauern') {
				throw new UnknownCommandException($this);
			}
			$count = $this->phrase->getParameter($p++);
		}
		$commodity = $this->phrase->getParameter($p);

		$this->parseObject($count, $commodity);
		if ($this->commodity instanceof Everything) {
			$this->dismissEverything();
		} elseif ($this->commodity instanceof Peasant) {
			$this->dismissPeasants();
		} else {
			$this->dismiss();
		}
	}

	private function dismissEverything(): void {
		$inventory = $this->unit->Inventory();
		$i         = $inventory->count();
		if ($i > 0) {
			foreach (array_keys(Allocation::POOL_COMMODITIES) as $commodity) {
				if (isset($inventory[$commodity])) {
					$this->giftToRegion($inventory[$commodity]);
				}
			}
			$inventory->clear();
		}

		$s = $this->unit->Size();
		if ($s > 0) {
			$this->peasantsToRegion($s);
			$this->unit->setSize(0);
		}

		if ($i + $s > 0) {
			$this->message(DismissEverythingMessage::class);
		} else {
			$this->message(DismissNothingMessage::class);
		}
	}

	private function dismiss(): void {
		$commodity = get_class($this->commodity);
		$quantity  = new Quantity($this->commodity, $this->amount);
		$inventory = $this->unit->Inventory();
		$reserve   = $inventory[$this->commodity] ?? null;
		if (isset(Allocation::POOL_COMMODITIES[$commodity])) {
			if ($reserve) {
				if ($this->amount < PHP_INT_MAX) {
					if ($reserve->Count() > $this->amount) {
						$this->giftToRegion($quantity);
						$this->unit->Inventory()->remove($quantity);
						$this->message(DismissMessage::class)->i($quantity);
					} else {
						$this->giftToRegion($reserve);
						unset($inventory[$this->commodity]);
						if ($reserve->Count() < $this->amount) {
							$this->message(DismissOnlyMessage::class)->i($reserve);
						} else {
							$this->message(DismissAllMessage::class)->s($this->commodity);
						}
					}
				} else {
					$this->giftToRegion($reserve);
					unset($inventory[$this->commodity]);
					$this->message(DismissAllMessage::class)->s($this->commodity);
				}
			} else {
				$this->message(DismissNoneMessage::class)->s($this->commodity);
			}
		} else {
			if ($reserve) {
				if ($this->amount < PHP_INT_MAX) {
					if ($reserve->Count() > $this->amount) {
						$this->unit->Inventory()->remove($quantity);
						$this->message(LoseMessage::class)->i($quantity);
					} else {
						unset($inventory[$this->commodity]);
						if ($reserve->Count() < $this->amount) {
							$this->message(LoseOnlyMessage::class)->i($reserve);
						} else {
							$this->message(LoseAllMessage::class)->s($this->commodity);
						}
					}
				} else {
					unset($inventory[$this->commodity]);
					$this->message(LoseAllMessage::class)->s($this->commodity);
				}
			} else {
				$this->message(LoseNothingMessage::class)->s($this->commodity);
			}
		}
	}
}
