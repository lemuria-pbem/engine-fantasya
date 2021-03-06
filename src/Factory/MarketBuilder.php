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

class MarketBuilder
{
	use BuilderTrait;

	public const INITIAL_PRICE_FACTOR = 10;

	protected const LUXURIES = [Balsam::class, Fur::class, Gem::class, Myrrh::class, Oil::class, Olibanum::class, Silk::class, Spice::class];

	public function __construct(private Intelligence $intelligence) {
	}

	public function initPrices(): void {
		$luxuries = $this->initLuxuries();
		$offer    = $luxuries->Offer();
		/** @var Luxury $luxury */
		$luxury = $luxuries->Offer()->Commodity();
		$offer->setPrice($luxury->Value());
		foreach ($luxuries as $demand /* @var Offer $demand */) {
			/** @var Luxury $luxury */
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
		$n     = count(self::LUXURIES);
		$class = self::LUXURIES[rand(0, $n - 1)];
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
