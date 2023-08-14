<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Simulation;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Event\Support as RealSupport;
use Lemuria\Engine\Fantasya\Factory\CollectTrait;
use Lemuria\Engine\Fantasya\Message\Filter\TravelSimulationFilter;
use Lemuria\Engine\Fantasya\Message\Unit\SupportNothingMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Engine\Fantasya\Turn\CherryPicker;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Census;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

/**
 * Pay every unit's upkeep.
 */
final class Support extends AbstractEvent
{
	use BuilderTrait;
	use CollectTrait;

	private Commodity $silver;

	private CherryPicker $cherryPicker;

	private TravelSimulationFilter $filter;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->silver       = self::createCommodity(Silver::class);
		$this->cherryPicker = State::getInstance()->getTurnOptions()->CherryPicker();
		$this->filter       = new TravelSimulationFilter();
	}

	protected function run(): void {
		$hungry = new People();
		foreach (Party::all() as $party) {
			if (!$this->isValidParty($party)) {
				continue;
			}
			$census = new Census($party);
			foreach ($census->getAtlas() as $region) {
				$hungry->clear();
				foreach ($census->getPeople($region) as $unit) {
					if (!$this->payFromInventory($unit)) {
						$hungry->add($unit);
					}
				}
				foreach ($hungry as $unit) {
					$filtered = false;
					foreach (Lemuria::Report()->getAll($unit) as $message) {
						if ($this->filter->retains($message)) {
							$filtered = true;
							break;
						}
					}
					if ($filtered || !$this->payFromResourcePool($unit) && !$this->payFromRealmFund($unit)) {
						$this->message(SupportNothingMessage::class, $unit);
					}
				}
			}
		}
	}

	private function isValidParty(Party $party): bool {
		return !$party->hasRetired() && $party->Type() === Type::Player && $this->cherryPicker->pickParty($party);
	}

	private function payFromInventory(Unit $unit): bool {
		$support = $this->support($unit);
		if ($support <= 0) {
			return true;
		}
		$inventory = $unit->Inventory();
		$ownSilver = $inventory->offsetGet($this->silver)->Count();
		if ($ownSilver >= $support) {
			$quantity = new Quantity($this->silver, $support);
			$inventory->remove($quantity);
			return true;
		}
		return false;
	}

	private function payFromResourcePool(Unit $unit): bool {
		$support = $this->support($unit);
		$this->collectQuantity($unit, $this->silver, $support);
		return $this->payOwnSupportIfPossible($unit, $support);
	}

	/**
	 * @noinspection DuplicatedCode
	 */
	private function payFromRealmFund(Unit $unit): bool {
		$region = $unit->Region();
		$realm  = $region->Realm();
		if ($unit->Party() === $realm?->Party() && $region !== $realm->Territory()->Central()) {
			$support   = $this->support($unit);
			$inventory = $unit->Inventory();
			$ownSilver = $inventory->offsetGet($this->silver)->Count();
			$inventory->add($this->context->getRealmFund($realm)->take(new Quantity($this->silver, $support - $ownSilver)));
			return $this->payOwnSupportIfPossible($unit, $support);
		}
		return false;
	}

	private function support(Unit $unit): int {
		$support  = RealSupport::SILVER;
		$support += $unit->Construction()?->Building()?->Feed() ?? 0;
		return $unit->Size() * $support;
	}

	private function payOwnSupportIfPossible(Unit $unit, int $support): bool {
		$inventory = $unit->Inventory();
		$ownSilver = $inventory->offsetGet($this->silver)->Count();
		if ($ownSilver >= $support) {
			$quantity = new Quantity($this->silver, $support);
			$unit->Inventory()->remove($quantity);
			return true;
		}
		return false;
	}
}
