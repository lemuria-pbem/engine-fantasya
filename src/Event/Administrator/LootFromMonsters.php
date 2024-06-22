<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

/**
 * This event collects all loot owned by monsters and moves it to a unit.
 */
final class LootFromMonsters extends AbstractEvent
{
	use BuilderTrait;
	use OptionsTrait;

	public final const string UNIT = 'unit';

	private Unit $unit;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	public function setOptions(array $options): LootFromMonsters {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$this->unit = Unit::get($this->getIdOption(self::UNIT));
	}

	protected function run(): void {
		$collection = $this->unit->Inventory();
		foreach ($this->unit->Region()->Residents() as $unit) {
			if ($unit->Party()->Type() === Type::Monster && !$unit->Construction() && !$unit->Vessel()) {
				foreach ($unit->Inventory() as $loot) {
					$collection->add(new Quantity($loot->Commodity(), $loot->Count()));
					Lemuria::Log()->debug($this->unit . ' takes ' . $loot . ' from ' . $unit);
				}
				$unit->Inventory()->clear();
			}
		}
	}
}
