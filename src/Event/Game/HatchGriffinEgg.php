<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Message\Party\HatchGriffinEggMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Griffin;
use Lemuria\Model\Fantasya\Commodity\Griffinegg;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

/**
 * For all parties one griffin egg is hatched.
 */
final class HatchGriffinEgg extends AbstractEvent
{
	use BuilderTrait;

	/**
	 * @var Unit[]
	 */
	private array $unit = [];

	private Commodity $griffin;

	private Commodity $egg;

	public function __construct(State $state) {
		parent::__construct($state, Priority::BEFORE);
		$this->griffin = self::createCommodity(Griffin::class);
		$this->egg     = self::createCommodity(Griffinegg::class);
	}

	protected function initialize(): void {
		foreach (Lemuria::Catalog()->getAll(Domain::PARTY) as $party /* @var Party $party */) {
			foreach ($party->People() as $unit /* @var Unit $unit */) {
				if ($unit->Inventory()[$this->egg]->Count()) {
					$this->unit[] = $unit;
					break;
				}
			}
		}
	}

	protected function run(): void {
		foreach ($this->unit as $unit) {
			$inventory = $unit->Inventory();
			$egg       = new Quantity($this->egg);
			$griffin   = new Quantity($this->griffin);
			$inventory->remove($egg)->add($griffin);
			$this->message(HatchGriffinEggMessage::class, $unit->Party())->e($unit);
		}
	}
}
