<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Destroy;

use Lemuria\Engine\Fantasya\Allocation;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\GiftTrait;
use Lemuria\Engine\Fantasya\Factory\Model\Everything;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Message\Unit\DismissAllMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DismissEverythingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DismissMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DismissNoneMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DismissNothingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DismissOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DismissSiegeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LoseAllMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LoseMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LoseNothingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LoseOnlyMessage;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Container;
use Lemuria\Model\Fantasya\Quantity;

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
 * - GIB Bauern|Region
 * - GIB Bauern|Region Alles
 * - GIB Bauern|Region <commodity>
 * - GIB Bauern|Region Person|Personen
 * - GIB Bauern|Region Alles <commodity>
 * - GIB Bauern|Region <amount> <commodity>
 * - GIB Bauern|Region <amount> Person|Personen
 */
final class Dismiss extends UnitCommand
{
	use GiftTrait;
	use SiegeTrait;

	protected function run(): void {
		$p = 1;
		$count = $this->phrase->getParameter($p++);
		if ($this->phrase->getVerb() === 'GIB') {
			if (!in_array(strtolower($count), ['bauern', 'region'])) {
				throw new UnknownCommandException($this);
			}
			$count = $this->phrase->getParameter($p++);
		}
		$commodity = $this->phrase->getLine($p);

		$this->parseObject($count, $commodity);
		if ($this->isSieged($this->unit->Construction())) {
			$this->message(DismissSiegeMessage::class);
		} else if ($this->commodity instanceof Everything) {
			$this->dismissEverything();
		} elseif ($this->commodity instanceof Peasant) {
			$this->dismissPeasants();
		} elseif ($this->commodity instanceof Container) {
			$this->commodity->setResources($this->unit->Inventory());
			foreach ($this->commodity->Commodities() as $commodity /** @var Commodity $commodity */) {
				$this->dismiss($commodity);
			}
		} else {
			$this->dismiss($this->commodity);
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

	private function dismiss(Commodity $commodity): void {
		$quantity  = new Quantity($commodity, $this->amount);
		$inventory = $this->unit->Inventory();
		$reserve   = $inventory[$commodity] ?? null;
		if (isset(Allocation::POOL_COMMODITIES[$commodity::class])) {
			if ($reserve) {
				if ($this->amount < PHP_INT_MAX) {
					if ($reserve->Count() > $this->amount) {
						$this->giftToRegion($quantity);
						$this->unit->Inventory()->remove($quantity);
						$this->message(DismissMessage::class)->i($quantity);
					} else {
						$this->giftToRegion($reserve);
						unset($inventory[$commodity]);
						if ($reserve->Count() < $this->amount) {
							$this->message(DismissOnlyMessage::class)->i($reserve);
						} else {
							$this->message(DismissAllMessage::class)->s($commodity);
						}
					}
				} else {
					$this->giftToRegion($reserve);
					unset($inventory[$commodity]);
					$this->message(DismissAllMessage::class)->s($commodity);
				}
			} else {
				$this->message(DismissNoneMessage::class)->s($commodity);
			}
		} else {
			if ($reserve) {
				if ($this->amount < PHP_INT_MAX) {
					if ($reserve->Count() > $this->amount) {
						$this->unit->Inventory()->remove($quantity);
						$this->message(LoseMessage::class)->i($quantity);
					} else {
						unset($inventory[$commodity]);
						if ($reserve->Count() < $this->amount) {
							$this->message(LoseOnlyMessage::class)->i($reserve);
						} else {
							$this->message(LoseAllMessage::class)->s($commodity);
						}
					}
				} else {
					unset($inventory[$commodity]);
					$this->message(LoseAllMessage::class)->s($commodity);
				}
			} else {
				$this->message(LoseNothingMessage::class)->s($commodity);
			}
		}
	}
}
