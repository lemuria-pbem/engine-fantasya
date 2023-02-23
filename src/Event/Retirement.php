<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Message\Party\RetirementMessage;
use Lemuria\Engine\Fantasya\Message\Party\RetirementPartyMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;

/**
 * Retire parties that have no units left.
 */
final class Retirement extends AbstractEvent
{
	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	protected function run(): void {
		Lemuria::Log()->debug('Running Retirement check.');
		$parties   = Party::all();
		$all       = count($parties);
		$active    = [];
		$retired   = 0;
		$nonPlayer = 0;
		$deceased  = [];

		foreach ($parties as $party) {
			if ($party->Type() !== Type::Player) {
				$nonPlayer++;
				continue;
			}
			if ($party->hasRetired()) {
				$retired++;
				continue;
			}
			if ($party->People()->count() > 0) {
				$active[] = $party;
				continue;
			}
			$deceased[] = $party->retire();
		}

		Lemuria::Log()->debug('This game has ' . $all . ' parties, of which ' . $nonPlayer . ' are non-player parties.');
		Lemuria::Log()->debug(count($active) . ' parties are active.');
		if (empty($deceased)) {
			Lemuria::Log()->debug($retired . ' parties have retired before. No new retirements.');
		} else {
			Lemuria::Log()->debug($retired . ' parties have retired before.');
			foreach ($deceased as $party) {
				Lemuria::Log()->debug('The party ' . $party . ' has no units left and retires now.');
				$this->message(RetirementMessage::class, $party);
				foreach ($active as $other) {
					$relations = [];
					$diplomacy = $other->Diplomacy();
					if ($diplomacy->isKnown($party)) {
						$diplomacy->Acquaintances()->remove($party);
						foreach ($diplomacy as $relation) {
							if ($relation->Party() === $party) {
								$relations[] = $relation;
							}
						}
						foreach ($relations as $relation) {
							$diplomacy->delete($relation);
						}
						$this->message(RetirementPartyMessage::class, $other)->e($party)->p($party->Name());
					}
				}
			}
		}
	}
}
