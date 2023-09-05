<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Event\Game\MiningDiscovery;
use Lemuria\Engine\Fantasya\Event\Game\ZombieInfection;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

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
		MiningDiscovery::addMiningDiscoveries($this->delegates);
		ZombieInfection::addZombieInfections($this->delegates);
	}
}
