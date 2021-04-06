<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Message\Party\AcquaintanceMessage;
use Lemuria\Engine\Fantasya\Message\Party\AcquaintanceTellMessage;
use Lemuria\Engine\Fantasya\Message\Party\AcquaintanceTellDisguiseMessage;
use Lemuria\Engine\Fantasya\Message\Party\AcquaintanceToldMessage;
use Lemuria\Engine\Fantasya\Outlook;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Census;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Relation;
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
			$census  = new Census($party);
			$outlook = new Outlook($census);
			foreach ($census->getAtlas() as $region /* @var Region $region */) {
				// First collect own units with their (maybe disguised) party.
				$ids = [];
				foreach ($census->getPeople($region) as $unit /* @var Unit $unit */) {
					$id = $census->getParty($unit)?->Id()->Id();
					if ($id) {
						$ids[$id] = true;
					}
				}
				// Then collect foreign units for the later telling of information.
				foreach ($region->Estate() as $construction /* @var Construction $construction */) {
					foreach ($construction->Inhabitants() as $unit /* @var Unit $unit */) {
						$this->addToNetwork($ids, $unit, $census);
					}
				}
				foreach ($region->Fleet() as $vessel /* @var Vessel $vessel */) {
					foreach ($vessel->Passengers() as $unit /* @var Unit $unit */) {
						$this->addToNetwork($ids, $unit, $census);
					}
				}
				foreach ($outlook->Apparitions($region) as $unit /* @var Unit $unit*/) {
					$this->addToNetwork($ids, $unit, $census);
				}
			}
		}
	}

	protected function run(): void {
		foreach ($this->network as $id => $network) {
			$party = Party::get(new Id($id));
			foreach ($network as $fid => $pair) {
				$foreign = Party::get(new Id($fid));
				/** @var Census $census */
				$census = $pair[0];
				/** @var Unit $unit */
				$unit = $pair[1];
				$realParty   = $census->Party();
				$diplomacy   = $realParty->Diplomacy();
				$realForeign = $unit->Party();

				// Party meets foreign party (maybe disguised) for the first time.
				if (!$diplomacy->knows($foreign)) {
					$diplomacy->knows($foreign);
					$this->message(AcquaintanceMessage::class, $realParty)->p($foreign->Name());
				}
				// Unit tells party people information (maybe disguised).
				$acquaintances = $realForeign->Diplomacy()->Acquaintances();
				if (!$acquaintances->isTold($party) && $diplomacy->has(Relation::TELL, $unit)) {
					$acquaintances->tell($party);
					if ($party === $realParty) {
						$this->message(AcquaintanceTellMessage::class, $realParty)->p($foreign->Name())->e($unit);
					} else {
						$this->message(AcquaintanceTellDisguiseMessage::class, $realParty)->p($foreign->Name())->p($party->Name(), AcquaintanceTellDisguiseMessage::DISGUISED)->e($unit);
					}
					$this->message(AcquaintanceToldMessage::class, $realForeign)->p($party->Name())->e($unit);
				}
			}
		}
	}

	/**
	 * @param array(int=>array) $ids
	 */
	private function addToNetwork(array $ids, Unit $unit, Census $census): void {
		if ($unit->Party() === $census->Party()) {
			return; // Disguised units from same parties know the truth.
		}
		foreach ($ids as $id) {
			$foreign                    = $census->getParty($unit);
			$fid                        = $foreign ? $foreign->Id()->Id() : 0;
			$this->network[$id][$fid][] = [$census, $unit];
		}
	}
}
