<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Destroy;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\GiftTrait;
use Lemuria\Engine\Fantasya\Factory\Model\Everything;
use Lemuria\Engine\Fantasya\Message\Unit\LoseEmptyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LoseEverythingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LoseToNoneMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LoseMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LoseToUnitMessage;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Quantity;

/**
 * Implementation of command VERLIEREN.
 *
 * The command donates a unit's commodities to random units of other parties and releases its persons to the peasant
 * population of a region.
 *
 * - VERLIEREN
 * - VERLIEREN Alles
 * - VERLIEREN <commodity>
 * - VERLIEREN Person|Personen
 * - VERLIEREN Alles <commodity>
 * - VERLIEREN <amount> <commodity>
 * - VERLIEREN <amount> Person|Personen
 *
 * - GIB 0
 * - GIB 0 Alles
 * - GIB 0 <commodity>
 * - GIB 0 Person|Personen
 * - GIB 0 Alles <commodity>
 * - GIB 0 <amount> <commodity>
 * - GIB 0 <amount> Person|Personen
 */
final class Lose extends UnitCommand
{
	use GiftTrait;

	protected function run(): void {
		$p = 1;
		$count = $this->phrase->getParameter($p++);
		if ($this->phrase->getVerb() === 'GIB') {
			if (strtolower($count) !== '0') {
				throw new UnknownCommandException($this);
			}
			$count = $this->phrase->getParameter($p++);
		}
		$commodity = $this->phrase->getParameter($p);

		$this->parseObject($count, $commodity);
		if ($this->commodity instanceof Everything) {
			$this->loseEverything();
		} elseif ($this->commodity instanceof Peasant) {
			$this->dismissPeasants();
		} else {
			$this->lose();
		}
	}

	protected function loseEverything(): void {
		$inventory = $this->unit->Inventory();
		$i         = $inventory->count();
		if ($i > 0) {
			$unit = null;
			foreach ($inventory as $quantity) {
				$unit = $this->giftToRandomUnit($quantity);
				if ($unit) {
					$this->message(LoseToUnitMessage::class, $unit)->e($this->unit)->i($quantity);
				}
			}
			$inventory->clear();
			if (!$unit) {
				$this->message(LoseToNoneMessage::class);
			}
		}

		$s = $this->unit->Size();
		if ($s > 0) {
			$this->peasantsToRegion($s);
			$this->unit->setSize(0);
		}

		if ($i + $s > 0) {
			$this->message(LoseEverythingMessage::class);
		} else {
			$this->message(LoseEmptyMessage::class);
		}
	}

	protected function lose(): void {
		$quantity = new Quantity($this->commodity, $this->amount);
		$unit     = $this->giftToRandomUnit($quantity);
		if ($unit) {
			$this->message(LoseToUnitMessage::class, $unit)->e($this->unit)->i($quantity);
		} else {
			$this->message(LoseMessage::class)->i($quantity);
		}
	}
}
