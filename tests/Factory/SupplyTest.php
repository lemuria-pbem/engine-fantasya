<?php
declare (strict_types = 1);
namespace Lemuria\Tests\Engine\Fantasya\Factory;

use Lemuria\Exception\LemuriaException;
use Lemuria\Exception\SingletonException;
use Lemuria\Model\Fantasya\Commodity\Luxury\Silk;
use Lemuria\Model\Fantasya\Offer;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use SATHub\PHPUnit\Base;

use Lemuria\Engine\Fantasya\Factory\Supply;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Luxuries;
use Lemuria\Model\Fantasya\Luxury;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;

class SupplyTest extends Base
{
	protected const int PEASANTS = 1050;

	protected Region $region;

	protected function luxury(string $class): Luxury {
		return new $class();
	}

	protected function region(?Luxury $offer = null, ?Luxury $demand = null, ?int $demandPrice = null, int $peasants = self::PEASANTS): Region {
		$this->region = new Region();
		$this->region->Resources()->add(new Quantity(new Peasant(), $peasants));
		$luxuries = new Luxuries();
		if ($demand) {
			$luxuries->offsetSet($demand, $demandPrice ?? $demand->Value());
		}
		return $this->region->setLuxuries($luxuries);
	}

	#[Test]
	public function regionProperty(): void {
		$supply = new Supply($this->region());

		$this->assertSame($this->region, $supply->Region());
	}

	#[Test]
	public function luxuryProperty(): void {
		$supply = new Supply($this->region());
		$this->expectException(LemuriaException::class);

		$supply->Luxury();
	}
}
