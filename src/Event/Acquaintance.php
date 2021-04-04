<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Message\Party\AcquaintanceMessage;
use Lemuria\Engine\Fantasya\Outlook;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Census;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Fantasya\Vessel;

/**
 * Units of parties get in contact to each other (if they are not disguising) and tell basic information about their
 * parties.
 */
final class Acquaintance extends AbstractEvent
{
	private array $network = [];

	#[Pure] public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
	}

	protected function initialize(): void {
		foreach (Lemuria::Catalog()->getAll(Catalog::PARTIES) as $party /* @var Party $party */) {
			$id = $party->Id()->Id();
			$this->network[$id] = [];
			$census  = new Census($party);
			$outlook = new Outlook($census);
			foreach ($census->getAtlas() as $region /* @var Region $region */) {
				foreach ($region->Estate() as $construction /* @var Construction $construction */) {
					foreach ($construction->Inhabitants() as $unit /* @var Unit $unit */) {
						$this->addToNetwork($id, $unit, $census);
					}
				}
				foreach ($region->Fleet() as $vessel /* @var Vessel $vessel */) {
					foreach ($vessel->Passengers() as $unit /* @var Unit $unit */) {
						$this->addToNetwork($id, $unit, $census);
					}
				}
				foreach ($outlook->Apparitions($region) as $unit /* @var Unit $unit*/) {
					$this->addToNetwork($id, $unit, $census);
				}
			}
		}
	}

	protected function run(): void {
		foreach ($this->network as $id => $parties) {
			$party     = Party::get(new Id($id));
			$diplomacy = $party->Diplomacy();
			foreach (array_keys($parties) as $id) {
				$foreign = Party::get(new Id($id));
				if (!$diplomacy->isKnown($foreign)) {
					$diplomacy->knows($foreign);
					$this->message(AcquaintanceMessage::class, $party)->p($foreign->Name());
				}
			}
		}
	}

	private function addToNetwork(int $id, Unit $unit, Census $census): void {
		if ($unit->Party()->Id()->Id() !== $id) {
			$foreign = $census->getParty($unit)?->Id()->Id();
			if ($foreign) {
				$this->network[$id][$foreign] = true;
			}
		}
	}
}
