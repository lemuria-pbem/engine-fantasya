<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Realm;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Factory\Supply;
use Lemuria\Engine\Fantasya\Factory\UnitTrait;
use Lemuria\Engine\Fantasya\Merchant;
use Lemuria\Engine\Fantasya\Message\Unit\DistributorFleetMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Building\Site;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Luxury;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Realm;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Unit;

/**
 * Helper for central luxury trade in realms.
 */
class Distributor
{
	use BuilderTrait;
	use MessageTrait;
	use SiegeTrait;
	use UnitTrait;

	private readonly Realm $realm;

	private int $center;

	/**
	 * @var array<int, int>
	 */
	private array $availability;

	private State $state;

	private Fleet $fleet;

	/**
	 * @var array<int, Region>
	 */
	private array $regions = [];

	/**
	 * @var array<int, Supply>
	 */
	private array $supply = [];

	private Commodity $silver;

	public function __construct(Unit $unit, protected Context $context) {
		$this->unit   = $unit;
		$this->realm  = $unit->Region()->Realm();
		$this->center = $this->realm->Territory()->Central()->Id()->Id();
		$this->state  = State::getInstance();
		$this->fleet  = State::getInstance()->getRealmFleet($this->realm);
		foreach ($this->realm->Territory() as $region) {
			if ($this->isUnderSiege($region) || $this->getCheckByAgreement(Relation::TRADE)) {
				continue;
			}
			if ($this->state->getIntelligence($region)->getCastle()?->Size() > Site::MAX_SIZE) {
				$this->regions[$region->Id()->Id()] = $region;
				//$this->supply[$region->Id()->Id()] = new Supply($region);
			}
		}
		$this->silver = self::createCommodity(Silver::class);
	}

	public function Realm(): Realm {
		return $this->realm;
	}

	public function Regions(): array {
		return $this->regions;
	}

	public function distribute(Merchant $merchant): void {
		if (empty($this->regions)) {
			Lemuria::Log()->debug('There is no supply to trade in realm ' . $this->realm . '.');
			return;
		}

		$unit  = $merchant->Unit();
		$isBuy = $merchant->Type() === Merchant::BUY;
		foreach ($merchant->getGoods() as $demand) {
			$capacity = $isBuy ? $this->fleet->Incoming() : $this->fleet->Outgoing();
			/** @var Luxury $luxury */
			$luxury   = $demand->Commodity();
			$total    = $demand->Count();
			$value    = $luxury->Value();
			$weight   = $luxury->Weight();
			$quantity = new Quantity($luxury, (int)floor($capacity / $weight));
			$this->message(DistributorFleetMessage::class, $unit)->p($capacity)->i($quantity);
			Lemuria::Log()->debug('Merchant ' . $unit . ' wants to ' . ($isBuy ? 'buy' : 'sell') . ' ' . $demand . ' in realm ' . $this->realm . '.');

			$price = [];
			$step  = [];
			$max   = [];
			foreach ($this->regions as $id => $region) {
				$supply            = $this->state->getSupply($region, $luxury);
				$price[$id]        = $supply->Price();
				$step[$id]         = $supply->getStep();
				$max[$id]          = $supply->count();
				$this->supply[$id] = $supply;
			}
			arsort($step);

			$plan = [];
			while ($total > 0) {
				$fleet = (int)floor($capacity / $weight);
				if ($fleet <= 0) {
					Lemuria::Log()->debug('There is no more transport capacity in realm ' . $this->realm . '.');
					break;
				}
				if ($isBuy) {
					asort($price);
				} else {
					arsort($price);
				}
				$id = key($price);
				if ($max[$id] <= 0) {
					Lemuria::Log()->debug('There is no more supply for ' . $luxury . ' in realm ' . $this->realm . '.');
					break;
				}
				$next = min($step[$id], $fleet);
				if ($next < $total) {
					$price[$id] += $value;
					$plan[$id][] = $next;
					$max[$id]   -= $next;
					$total      -= $next;
					if ($id !== $this->center) {
						$capacity -= $next * $weight;
					}
				} else {
					$plan[$id][] = $total;
					$total       = 0;
				}
			}
			if ($total > 0) {
				$plan[$this->center][] = $total;
			}

			$trade = [];
			$total = 0;
			foreach ($plan as $id => $trades) {
				$amount     = array_sum($trades);
				$trade[$id] = $amount;
				$total     += $this->supply[$id]->estimate($amount);
			}
			$merchant->costEstimation($total);
			Lemuria::Log()->debug('Cost estimation to ' . ($isBuy ? 'buy ' : 'sell ') . $luxury . ' in realm ' . $this->realm . ' is ' . $total . ' silver.');

			$trades = $this->state->getWorkload($unit);
			foreach ($trade as $id => $amount) {
				$supply       = $this->supply[$id];
				$region       = $supply->Region();
				$resources    = $region->Resources();
				$regionSilver = $resources[Silver::class]->Count();
				if (!$isBuy) {
					Lemuria::Log()->debug('The peasants in ' . $region . ' have ' . $regionSilver . ' left for trading.');
				}
				$traded = 0;
				$silver = 0;
				for ($i = 0; $i < $amount; $i++) {
					if ($trades->CanWork()) {
						$price = $supply->ask();
						if ($isBuy && $regionSilver < $price) {
							Lemuria::Log()->debug('The peasants have no more silver to buy luxuries.');
							break;
						}
						if ($merchant->trade($luxury, $price)) {
							$supply->one();
							if ($isBuy) {
								$resources->add(new Quantity($this->silver, $price));
								$regionSilver += $price;
							} else {
								$resources->remove(new Quantity($this->silver, $price));
								$regionSilver -= $price;
							}
							$trades->add();
							$traded++;
							$silver += $price;
						} else {
							Lemuria::Log()->debug('Merchant ' . $merchant . ' cannot trade any more ' . $luxury . '.');
						}
					} else {
						Lemuria::Log()->debug('Merchant ' . $merchant . ' has no more trades.');
					}
				}
				if ($id !== $this->center) {
					if ($isBuy) {
						$this->fleet->fetch($traded * $weight);
						$this->fleet->send($silver * $this->silver->Weight());
					} else {
						$this->fleet->send($traded * $weight);
						$this->fleet->fetch($silver * $this->silver->Weight());
					}
				}
				$merchant->finish();
				Lemuria::Log()->debug('Merchant ' . $merchant . ' has ' . ($isBuy ? 'bought' : 'sold') . ' ' . $traded . ' ' . $luxury . ' in region ' . $region . '.');
			}
		}
	}
}