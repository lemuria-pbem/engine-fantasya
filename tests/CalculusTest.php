<?php
declare(strict_types = 1);
namespace Lemuria\Tests\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Landscape\Plain;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Race\Human;
use Lemuria\Model\Fantasya\Talent\Stamina;

use Lemuria\Tests\Model\Fantasya\Mock\RegionMock;
use Lemuria\Tests\Model\Fantasya\Mock\UnitMock;
use Lemuria\Tests\Model\Fantasya\ModelTest;

class CalculusTest extends ModelTest
{
	use BuilderTrait;

	protected final const KNOWLEDGE = [Stamina::class => 5];

	protected final const INVENTORY = [Silver::class => 500];

	protected UnitMock $unit;

	protected function setUp(): void {
		parent::setUp();
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
	}

	/**
	 * @test
	 */
	public function construct(): Calculus {
		$calculus = new Calculus($this->unit);

		$this->assertNotNull($calculus);

		return $calculus;
	}

	/**
	 * @test
	 */
	public function unit(): void {
		$calculus = new Calculus($this->unit);

		$this->assertSame($this->unit, $calculus->Unit());
	}

	/**
	 * @test
	 * @depends construct
	 */
	public function knowledge(Calculus $calculus): void {
		$this->assertSame(5, $calculus->knowledge(Stamina::class)->Level());
	}

	/**
	 * @test
	 * @depends construct
	 */
	public function payload(Calculus $calculus): void {
		$this->assertSame(10 * 750, $calculus->payload());
	}
}
