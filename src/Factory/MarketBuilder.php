<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Model\Fantasya\Commodity\Luxury\Balsam;
use Lemuria\Model\Fantasya\Commodity\Luxury\Fur;
use Lemuria\Model\Fantasya\Commodity\Luxury\Gem;
use Lemuria\Model\Fantasya\Commodity\Luxury\Myrrh;
use Lemuria\Model\Fantasya\Commodity\Luxury\Oil;
use Lemuria\Model\Fantasya\Commodity\Luxury\Olibanum;
use Lemuria\Model\Fantasya\Commodity\Luxury\Silk;
use Lemuria\Model\Fantasya\Commodity\Luxury\Spice;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Intelligence;
use Lemuria\Model\Fantasya\Luxuries;
use Lemuria\Model\Fantasya\Luxury;
use Lemuria\Model\Fantasya\Offer;
use function Lemuria\randElement;

class MarketBuilder
{
	use BuilderTrait;

	public final const int INITIAL_PRICE_FACTOR = 10;

	/**
	 * @type array<string>
	 */
	protected final const array LUXURIES = [Balsam::class, Fur::class, Gem::class, Myrrh::class, Oil::class, Olibanum::class, Silk::class, Spice::class];

	public function __construct(private readonly Intelligence $intelligence) {
	}

	public function initPrices(): void {
		$luxuries = $this->initLuxuries();
		$offer    = $luxuries->Offer();
		$luxury   = $luxuries->Offer()->Commodity();
		$offer->setPrice($luxury->Value());
		foreach ($luxuries as $demand) {
			$luxury = $demand->Commodity();
			$demand->setPrice(self::INITIAL_PRICE_FACTOR * $luxury->Value());
		}
	}

	protected function initLuxuries(): Luxuries {
		$region = $this->intelligence->Region();
		$luxuries = $region->Luxuries();
		if (!$luxuries) {
			$luxuries = new Luxuries($this->initOffer());
			$region->setLuxuries($this->initDemand($luxuries));
		}
		return $luxuries;
	}

	protected function initOffer(): Offer {
		$class = randElement(self::LUXURIES);
		/** @var Luxury $luxury */
		$luxury = self::createCommodity($class);
		return new Offer($luxury, $luxury->Value());
	}

	protected function initDemand(Luxuries $luxuries): Luxuries {
		$classes = self::LUXURIES;
		$offer  = $luxuries->Offer()->Commodity()::class;
		unset($classes[$offer]);
		foreach ($classes as $class) {
			/** @var Luxury $luxury */
			$luxury           = self::createCommodity($class);
			$luxuries[$class] = new Offer($luxury, $luxury->Value());
		}
		return $luxuries;
	}
}
