<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Message\Region\MarketUpdateDemandMessage;
use Lemuria\Engine\Fantasya\Message\Region\MarketUpdateOfferMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Engine\Fantasya\Statistics\StatisticsTrait;
use Lemuria\Engine\Fantasya\Statistics\Subject;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Building\Site;
use Lemuria\Model\Fantasya\Luxury;
use Lemuria\Model\Fantasya\Offer;
use Lemuria\Model\Fantasya\Region;

/**
 * Update luxury prices after commerce has been done.
 */
final class MarketUpdate extends AbstractEvent
{
	use StatisticsTrait;

	/**
	 * @var array(string=>true)
	 */
	private array $commerce = [];

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	protected function initialize(): void {
		Lemuria::Log()->debug('Setting luxury prices from commerce supplies.');
		foreach ($this->state->getAllCommerces() as $commerce) {
			$region   = $commerce->Region();
			$luxuries = $region->Luxuries();
			$offer    = $luxuries->Offer()->Commodity();
			foreach ($commerce->getSupplies() as $supply) {
				$commodity = $supply->Luxury();
				$price     = $supply->Price();
				if ($commodity === $offer) {
					$luxury = $luxuries->Offer();
					if ($price > $luxury->Price()) {
						$luxury->setPrice($price);
						$this->message(MarketUpdateOfferMessage::class, $region)->s($commodity)->p($price);
					}
				} else {
					$luxury = $luxuries[$commodity];
					if ($price < $luxury->Price()) {
						$luxury->setPrice($price);
						$this->message(MarketUpdateDemandMessage::class, $region)->s($commodity)->p($price);
					}
				}
				$this->commerce[$this->id($region, $commodity)] = true;
			}
		}
	}

	protected function run(): void {
		Lemuria::Log()->debug('Moving prices in all regions with a market.');
		foreach (Lemuria::Catalog()->getAll(Domain::Location) as $region /* @var Region $region */) {
			if ($this->hasMarket($region)) {
				$luxuries = $region->Luxuries();
				if ($luxuries) {
					$this->modifyOffer($region, $luxuries->Offer());
					foreach ($luxuries as $demand) {
						$this->modifyDemand($region, $demand);
					}
					$this->placeMetrics(Subject::Market, $region);
				}
			}
		}
	}

	private function hasMarket(Region $region): bool {
		$castle = $this->context->getIntelligence($region)->getGovernment();
		return $castle?->Size() > Site::MAX_SIZE;
	}

	private function modifyOffer(Region $region, Offer $offer): void {
		$luxury = $offer->Commodity();
		if (!isset($this->commerce[$this->id($region, $luxury)])) {
			$price = $offer->Price();
			if ($price > $luxury->Value()) {
				$offer->setPrice(--$price);
			}
		}
	}

	private function modifyDemand(Region $region, Offer $offer): void {
		$luxury = $offer->Commodity();
		if (!isset($this->commerce[$this->id($region, $luxury)])) {
			$price = $offer->Price();
			if ($price < 100 * $luxury->Value()) {
				$offer->setPrice(++$price);
			}
		}
	}

	private function id(Region $region, Luxury $luxury): string {
		return $region->Id()->Id() . '-' . getClass($luxury);
	}
}
