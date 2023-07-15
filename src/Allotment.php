<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Camel;
use Lemuria\Model\Fantasya\Commodity\Elephant;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Pegasus;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Realm;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Resources;

/**
 * Helper for central resource distribution in realms.
 */
class Allotment
{
	use BuilderTrait;

	public const POOL_COMMODITIES = [
		Silver::class => true, Camel::class  => true, Elephant::class => true, Horse::class => true, Pegasus::class => true
	];

	/**
	 * @var array<int, Region>
	 */
	private array $region;

	/**
	 * @var array<int, int>
	 */
	private array $availability;

	private int $availableSum;

	public function __construct(private readonly Realm $realm) {
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
			$total = min($quantity->Count(), $this->availableSum);
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

		$regulation = $this->realm->Party()->Regulation();
		foreach ($this->realm->Territory() as $region) {
			$id                      = $region->Id()->Id();
			$this->region[$id]       = $region;
			$threshold               = $regulation->getQuotas($region)?->getQuota($commodity)?->Threshold();
			$resource                = $region->Resources()->offsetGet($commodity)->Count();
			$reserve                 = $threshold === null ? $resource : max(0, (int)floor(($resource - $threshold) / $quota));
			$availability            = (int)floor($quota * $reserve);
			$this->availability[$id] = $availability;
			$this->availableSum     += $availability;
		}
	}
}
