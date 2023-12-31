<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Realm;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Factory\Model\RealmQuota;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Factory\UnitTrait;
use Lemuria\Engine\Fantasya\Message\Unit\DistributorFleetMessage;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Animal;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Engine\Fantasya\Consumer;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Realm;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Engine\Fantasya\State;

/**
 * Helper for central resource distribution in realms.
 */
class Allotment
{
	use MessageTrait;
	use SiegeTrait;
	use UnitTrait;

	private int $center;

	/**
	 * @var array<int, Region>
	 */
	private array $region;

	/**
	 * @var array<int, int>
	 */
	private array $availability;

	private State $state;

	private RealmQuota $quotas;

	private int|float|null $threshold = null;

	private Fleet $fleet;

	private bool $isFleetEnabled = true;

	public function __construct(private readonly Realm $realm, protected Context $context) {
		$this->center = $realm->Territory()->Central()->Id()->Id();
		$this->state  = State::getInstance();
		$this->quotas = new RealmQuota($realm);
		$this->fleet  = State::getInstance()->getRealmFleet($realm);
	}

	public function Realm(): Realm {
		return $this->realm;
	}

	public function setThreshold(int|float|null $threshold): static {
		$this->threshold = $threshold;
		return $this;
	}

	public function getAvailability(Consumer $consumer, Commodity $commodity): int {
		$this->unit = $consumer->Unit();
		$this->calculateAvailability($commodity, $consumer->getQuota());
		return array_sum($this->availability);
	}

	/**
	 * Start resource distribution.
	 */
	public function distribute(Consumer $consumer): void {
		$this->unit = $consumer->Unit();
		$resources  = new Resources();
		$quota      = $consumer->getQuota();
		foreach ($consumer->getDemand() as $quantity) {
			$commodity = $quantity->Commodity();
			$piece     = $commodity->Weight();
			$demand    = $quantity->Count();
			$this->calculateAvailability($commodity, $quota);
			$fleetTotal = $this->calculateFleetTotal($commodity);
			$local      = $this->availability[$this->center];
			$total      = $fleetTotal + $local;
			$parts      = [];
			if ($total > 0) {
				$rate = min(1.0, $demand / array_sum($this->availability));
				foreach ($this->region as $id => $region) {
					if ($id === $this->center) {
						continue;
					}
					$availability = $this->state->getAvailability($region);
					$available    = $availability->getResource($commodity)->Count();
					$part         = (int)ceil($rate * $this->availability[$id]);
					$part         = min($part, $demand, $available);
					if ($part > 0) {
						if ($this->isFleetEnabled) {
							if ($commodity instanceof Animal) {
								$part = $this->fleet->mount(new Quantity($commodity, $part))->Count();
							} else {
								$weight = $this->fleet->fetch($part * $piece);
								$part   = (int)floor($weight / $piece);
							}
						}
						$availability->remove(new Quantity($commodity, $part));
						$partQuantity = new Quantity($commodity, $part);
						$resources->add($partQuantity);
						$demand    -= $part;
						$parts[$id] = $part;
						Lemuria::Log()->debug('Allotment of ' . $partQuantity . ' in region ' . $id . ' for consumer ' . $consumer->getId() . '.');
					}
				}
			}
			$availability = $this->state->getAvailability($this->region[$this->center]);
			$available    = $availability->getResource($commodity)->Count();
			$remaining    = min($demand, $available, $local);
			if ($remaining > 0) {
				$availability->remove(new Quantity($commodity, $remaining));
				$partQuantity = new Quantity($commodity, $remaining);
				$resources->add($partQuantity);
				$parts[$this->center] = $demand;
				Lemuria::Log()->debug('Allotment of ' . $partQuantity . ' in region ' . $this->center . ' for consumer ' . $consumer->getId() . '.');
			}
			$total = array_sum($parts);
			foreach ($parts as $id => $part) {
				$consumer->addRegion($this->region[$id], $part / $total);
			}
		}
		$consumer->allocate($resources);
	}

	public function disableFleetCheck(): static {
		$this->isFleetEnabled = false;
		Lemuria::Log()->debug('Fleet check is disabled for this command');
		return $this;
	}

	protected function calculateAvailability(Commodity $commodity, float $quota): void {
		$this->region       = [];
		$this->availability = [];
		foreach ($this->realm->Territory() as $region) {
			if ($this->isUnderSiege($region) || $this->getCheckByAgreement(Relation::RESOURCES)) {
				continue;
			}
			$id                      = $region->Id()->Id();
			$this->region[$id]       = $region;
			$threshold               = $this->threshold !== null ? $this->threshold : $this->quotas->getQuota($region, $commodity)->Threshold();
			$resource                = $this->state->getAvailability($region)->getQuotaResource($commodity, $threshold)->Count();
			$this->availability[$id] = max(0, (int)floor($quota * $resource));
		}
	}

	protected function calculateFleetTotal(Commodity $commodity): int {
		$availableSum = array_sum($this->availability) - $this->availability[$this->center];
		if ($this->isFleetEnabled) {
			$capacity = $this->fleet->Incoming();
			if ($commodity instanceof Animal) {
				$fleetMaximum = $this->fleet->getMounts($commodity);
				$capacity     = min($capacity, $this->fleet->Outgoing());
			} else {
				$fleetMaximum = (int)floor($capacity / $commodity->Weight());
			}
			$quantity = new Quantity($commodity, $fleetMaximum);
			$this->message(DistributorFleetMessage::class, $this->unit)->p($capacity)->i($quantity);
			return min($availableSum, $fleetMaximum);
		}
		return $availableSum;
	}
}
