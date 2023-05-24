<?php
declare(strict_types = 1);
namespace Lemuria\Tests\Engine\Fantasya\Factory;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Factory\GearDistribution;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Commodity\Weapon\Bow;
use Lemuria\Model\Fantasya\Commodity\Weapon\Sword;
use Lemuria\Model\Fantasya\Distribution;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Landscape\Plain;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Race\Human;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Talent\Archery;
use Lemuria\Model\Fantasya\Talent\Bladefighting;

use Lemuria\Tests\Model\Fantasya\Mock\RegionMock;
use Lemuria\Tests\Model\Fantasya\Mock\UnitMock;
use Lemuria\Tests\Model\Fantasya\Model;

class GearDistributionTest extends Model
{
	use BuilderTrait;

	protected final const KNOWLEDGE = [Archery::class => 5, Bladefighting::class => 3];

	protected final const INVENTORY = [Bow::class => 7, Horse::class => 25, Silver::class => 505, Sword::class => 10];

	protected UnitMock $unit;

	protected Calculus $calculus;

	#[Before]
	protected function iniMock(): void {
		$this->unit = new UnitMock();
		$this->unit->setRace(self::createRace(Human::class))->setSize(10)->setBattleRow(BattleRow::Back);
		foreach (self::KNOWLEDGE as $talent => $level) {
			$this->unit->Knowledge()->add(new Ability(self::createTalent($talent), Ability::getExperience($level)));
		}
		foreach (self::INVENTORY as $commodity => $count) {
			$this->unit->Inventory()->add(new Quantity(self::createCommodity($commodity), $count));
		}

		$region = new RegionMock();
		$region->setLandscape(self::createLandscape(Plain::class));
		$this->unit->setRegion($region);

		$this->calculus = new Calculus($this->unit);
	}

	#[Test]
	public function construct(): GearDistribution {
		$gearDistribution = new GearDistribution($this->calculus);

		$this->assertNotNull($gearDistribution);

		return $gearDistribution;
	}

	#[Test]
	#[Depends('construct')]
	public function distribute(GearDistribution $gearDistribution): GearDistribution {
		$this->assertSame($gearDistribution, $gearDistribution->distribute());

		return $gearDistribution;
	}

	#[Test]
	#[Depends('distribute')]
	public function getDistributions(GearDistribution $gearDistribution): void {
		$distributions = $gearDistribution->getDistributions();

		$this->assertArray($distributions, 3, Distribution::class);

		$unitSize  = 0;
		$resources = new Resources();
		foreach ($distributions as $distribution) {
			$size      = $distribution->Size();
			$unitSize += $size;
			foreach ($distribution as $quantity) {
				$resources->add(new Quantity($quantity->Commodity(), $size * $quantity->Count()));
			}
		}

		$this->assertSame($this->unit->Size(), $unitSize);
		$this->assertItemSet(self::INVENTORY, $resources);
	}
}
