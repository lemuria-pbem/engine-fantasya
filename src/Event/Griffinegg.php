<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use function Lemuria\randChance;
use Lemuria\Engine\Fantasya\Message\Party\HatchGriffinEggMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Griffin;
use Lemuria\Model\Fantasya\Commodity\Griffinegg as GriffineggModel;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

/**
 * Griffin eggs in possession of units may transform into newborn griffins.
 */
final class Griffinegg extends AbstractEvent
{
	use BuilderTrait;

	private const CHANCE = 0.15;

	private Commodity $griffin;

	private Commodity $egg;

	public function __construct(State $state) {
		parent::__construct($state, Priority::AFTER);
		$this->griffin = self::createCommodity(Griffin::class);
		$this->egg     = self::createCommodity(GriffineggModel::class);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Domain::UNIT) as $unit /* @var Unit $unit */) {
			if ($unit->Inventory()[$this->egg]->Count()) {
				if (randChance(self::CHANCE)) {
					$this->hatchFor($unit);
				}
			}
		}
	}

	private function hatchFor(Unit $unit): void {
		$inventory = $unit->Inventory();
		$egg       = new Quantity($this->egg);
		$griffin   = new Quantity($this->griffin);
		$inventory->remove($egg)->add($griffin);
		$this->message(HatchGriffinEggMessage::class, $unit->Party())->e($unit);

	}
}
