<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Census;
use Lemuria\Engine\Fantasya\Message\Party\AcquaintanceMessage;
use Lemuria\Engine\Fantasya\Message\Party\AcquaintanceTellMessage;
use Lemuria\Engine\Fantasya\Message\Party\AcquaintanceTellDisguiseMessage;
use Lemuria\Engine\Fantasya\Message\Party\AcquaintanceToldMessage;
use Lemuria\Engine\Fantasya\Outlook;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Unit;

/**
 * Units of parties get in contact to each other (if they are not disguising) and tell basic information about their
 * parties.
 */
final class Acquaintance extends AbstractEvent
{
	use BuilderTrait;

	private array $network = [];

	private Talent $perception;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->perception = self::createTalent(Perception::class);
	}

	protected function run(): void {
		foreach (Party::all() as $party) {
			if ($party->Type() === Type::Monster || $party->hasRetired()) {
				continue;
			}

			$census  = new Census($party);
			$outlook = new Outlook($census);
			foreach ($census->getAtlas() as $region) {
				// First collect own units with their (maybe disguised) party.
				$ids = [];
				foreach ($census->getPeople($region) as $unit) {
					$calculus = new Calculus($unit);
					if ($calculus->isInvisible()) {
						continue;
					}
					$id = $census->getParty($unit)?->Id()->Id();
					if ($id) {
						if ($unit->IsHiding()) {
							$calculus   = new Calculus($unit);
							$camouflage = $calculus->camouflage()->Level();
						} else {
							$camouflage = 0;
						}
						$previous = $ids[$id] ?? $camouflage;
						$ids[$id] = min($previous, $camouflage);
					}
				}

				// Then collect foreign units for telling of information later.
				if ($region instanceof Region) {
					foreach ($region->Estate() as $construction) {
						foreach ($construction->Inhabitants() as $unit) {
							$this->addToNetwork($ids, $unit, $census);
						}
					}
					foreach ($region->Fleet() as $vessel) {
						foreach ($vessel->Passengers() as $unit) {
							$this->addToNetwork($ids, $unit, $census);
						}
					}
					foreach ($outlook->getApparitions($region) as $unit) {
						$this->addToNetwork($ids, $unit, $census);
					}
				}
			}
		}

		foreach ($this->network as $id => $network) {
			$party = Party::get(new Id($id));
			foreach ($network as $fid => $pairs) {
				if ($fid <= 0) {
					continue;
				}
				$foreign = Party::get(new Id($fid));
				foreach ($pairs as $pair) {
					/** @var Census $census */
					$census = $pair[0];
					/** @var Unit $unit */
					$unit        = $pair[1];
					$camouflage  = $pair[2];
					$realParty   = $census->Party();
					$diplomacy   = $realParty->Diplomacy();
					$realForeign = $unit->Party();

					// Party meets foreign party (maybe disguised) for the first time.
					if (!$diplomacy->isKnown($foreign)) {
						$diplomacy->knows($foreign);
						$this->message(AcquaintanceMessage::class, $realParty)->p($foreign->Name());
					}

					// Unit tells party people information (maybe disguised).
					$calculus   = new Calculus($unit);
					$perception = $calculus->knowledge($this->perception)->Level();
					if ($perception >= $camouflage) {
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
		}
	}

	/**
	 * @param array<int, array> $ids
	 */
	private function addToNetwork(array $ids, Unit $unit, Census $census): void {
		$calculus = new Calculus($unit);
		if ($calculus->isInvisible()) {
			return; // Invisible units are not considered.
		}
		if ($unit->Party() === $census->Party()) {
			return; // Disguised units from same parties know the truth.
		}
		foreach ($ids as $id => $camouflage) {
			$foreign = $census->getParty($unit);
			if ($foreign?->Type() !== Type::Monster) {
				$fid                        = $foreign ? $foreign->Id()->Id() : 0;
				$this->network[$id][$fid][] = [$census, $unit, $camouflage];
			}
		}
	}
}
