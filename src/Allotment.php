<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Factory\Model\RealmQuota;
use Lemuria\Engine\Fantasya\Realm\Fleet;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Realm;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Resources;

/**
 * Helper for central resource distribution in realms.
 */
class Allotment
{
	/**
	 * @var array<int, Region>
	 */
	private array $region;

	/**
	 * @var array<int, int>
	 */
	private array $availability;

	private RealmQuota $quotas;

	private Fleet $fleet;

	private int $availableSum;

	public function __construct(private readonly Realm $realm) {
		$this->quotas = new RealmQuota($realm);
		$this->fleet  = State::getInstance()->getRealmFleet($realm);
	}

	public function Realm(): Realm {
		return $this->realm;
	}

	/**
	 * Start resource distribution.
	 */
	public function distribute(Consumer $consumer): void {
		$resources = new Resources();
		$quota     = $consumer->getQuota();
		foreach ($consumer->getDemand() as $quantity) {
			$commodity = $quantity->Commodity();
			$this->calculateAvailability($commodity, $quota);
			$total = $this->calculateTotal($commodity, min($quantity->Count(), $this->availableSum));
			$rate  = $total / $this->availableSum;
			foreach ($this->region as $id => $region) {
				$part = (int)round($rate * $this->availability[$id]);
				if ($part > $total) {
					$part = $total;
				}
				if ($part > 0) {
					$region->Resources()->remove(new Quantity($commodity, $part));
					$resources->add(new Quantity($commodity, $part));
					$total -= $part;
					Lemuria::Log()->debug('Allotment of ' . $quantity . ' in region ' . $id . ' for consumer ' . $consumer->getId() . '.');
				}
			}
		}
		$consumer->allocate($resources);
	}

	protected function calculateAvailability(Commodity $commodity, float $quota): void {
		$this->region       = [];
		$this->availability = [];
		$this->availableSum = 0;

		foreach ($this->realm->Territory() as $region) {
			$id                      = $region->Id()->Id();
			$this->region[$id]       = $region;
			$threshold               = $this->quotas->getQuota($region, $commodity)->Threshold();
			$resource                = $region->Resources()->offsetGet($commodity)->Count();
			$reserve                 = max(0, (int)floor(($resource - $threshold) / $quota));
			$availability            = (int)floor($quota * $reserve);
			$this->availability[$id] = $availability;
			$this->availableSum     += $availability;
		}
	}

	protected function calculateTotal(Commodity $commodity, int $amount): int {
		$piece  = $commodity->Weight();
		$weight = $amount * $piece;
		$weight = $this->fleet->fetch($weight);
		return (int)floor($weight / $piece);
	}
}
