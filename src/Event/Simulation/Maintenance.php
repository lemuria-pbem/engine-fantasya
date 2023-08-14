<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Simulation;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Factory\CollectTrait;
use Lemuria\Engine\Fantasya\Message\Construction\UnmaintainedOvercrowdedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UpkeepNothingMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Engine\Fantasya\Turn\CherryPicker;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Estate;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;

/**
 * Maintain all constructions.
 */
final class Maintenance extends AbstractEvent
{
	use BuilderTrait;
	use CollectTrait;

	private Commodity $silver;

	private CherryPicker $cherryPicker;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Middle);
		$this->silver       = self::createCommodity(Silver::class);
		$this->cherryPicker = State::getInstance()->getTurnOptions()->CherryPicker();
	}

	protected function run(): void {
		$unmaintained = new Estate();
		$overcrowded  = new Estate();
		$estate       = new Estate();
		foreach (Region::all() as $region) {
			$estate->clear();
			foreach ($region->Estate() as $construction) {
				if ($this->isOvercrowded($construction)) {
					$overcrowded->add($construction);
					continue;
				}
				$party = $construction->Inhabitants()->Owner()?->Party();
				if (!$party || !$this->cherryPicker->pickParty($party)) {
					continue;
				}
				if (!$this->payFromInventory($construction)) {
					$estate->add($construction);
				}
			}
			foreach ($estate as $construction) {
				if (!$this->payFromResourcePool($construction) && !$this->payFromRealmFund($construction)) {
					$unmaintained->add($construction);
				}
			}
		}
		foreach ($overcrowded as $construction) {
			$this->message(UnmaintainedOvercrowdedMessage::class, $construction);
		}
		foreach ($unmaintained as $construction) {
			$owner = $construction->Inhabitants()->Owner();
			$this->message(UpkeepNothingMessage::class, $owner)->e($construction);
		}
	}

	private function payFromInventory(Construction $construction): bool {
		$upkeep = $construction->Building()->Upkeep();
		if ($upkeep <= 0) {
			return true;
		}
		$inventory = $construction->Inhabitants()->Owner()->Inventory();
		$ownSilver = $inventory->offsetGet($this->silver)->Count();
		if ($ownSilver >= $upkeep) {
			$quantity = new Quantity($this->silver, $upkeep);
			$inventory->remove($quantity);
			return true;
		}
		return false;
	}

	private function payFromResourcePool(Construction $construction): bool {
		$upkeep = $construction->Building()->Upkeep();
		$unit   = $construction->Inhabitants()->Owner();
		$this->collectQuantity($unit, $this->silver, $upkeep);
		return $this->payUpkeepIfPossible($unit, $upkeep);
	}

	private function payFromRealmFund(Construction $construction): bool {
		$region = $construction->Region();
		$unit   = $construction->Inhabitants()->Owner();
		$realm  = $region->Realm();
		if ($unit->Party() === $realm?->Party() && $region !== $realm->Territory()->Central()) {
			$upkeep    = $construction->Building()->Upkeep();
			$inventory = $unit->Inventory();
			$ownSilver = $inventory->offsetGet($this->silver)->Count();
			$inventory->add($this->context->getRealmFund($realm)->take(new Quantity($this->silver, $upkeep - $ownSilver)));
			return $this->payUpkeepIfPossible($unit, $upkeep);
		}
		return false;
	}

	private function payUpkeepIfPossible(Unit $unit, int $upkeep): bool {
		$inventory = $unit->Inventory();
		$ownSilver = $inventory->offsetGet($this->silver)->Count();
		if ($ownSilver >= $upkeep) {
			$quantity = new Quantity($this->silver, $upkeep);
			$unit->Inventory()->remove($quantity);
			return true;
		}
		return false;
	}

	private function isOvercrowded(Construction $construction): bool {
		return $construction->Size() < $construction->Inhabitants()->Size();
	}
}
