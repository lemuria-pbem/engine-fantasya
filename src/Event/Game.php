<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use function Lemuria\randInt;
use Lemuria\Engine\Fantasya\Event\Game\Spawn;
use Lemuria\Engine\Fantasya\Event\Game\ZombieInfection;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Monster\Zombie;
use Lemuria\Model\Fantasya\Party;

/**
 * The Game event adds other events on specific conditions.
 */
final class Game extends DelegatedEvent
{
	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	protected function createDelegates(): void {
		Lemuria::Log()->debug('Adding game events.');

		Lemuria::Log()->debug('Skipping ZombieInfection.');
		$regions = [];
		foreach (Party::get(Id::fromId(Spawn::ZOMBIES))->People() as $unit) {
			if ($unit->Race() instanceof Zombie) {
				$region                       = $unit->Region();
				$regions[$region->Id()->Id()] = true;
			}
		}
		foreach (array_keys($regions) as $id) {
			$event  = new ZombieInfection($this->state);
			$infect = (1.0 + (randInt(0, 40) - 20) / 100) * 0.01;
			$event->setOptions([ZombieInfection::REGION => $id, ZombieInfection::INFECT => $infect, ZombieInfection::CHANCE => 0.01]);
			//$this->delegates[] = $event;
		}
	}
}
