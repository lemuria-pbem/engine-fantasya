<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

/**
 * This event finishes the monsters' behaviour.
 */
final class Finish extends AbstractEvent
{
	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	protected function run(): void {
		$monsters = $this->state->getAllMonsters();
		Lemuria::Log()->debug('Behaviour of ' . count($monsters) . ' monsters is finished.');
		foreach ($this->state->getAllMonsters() as $behaviour) {
			$behaviour->finish();
		}
	}
}
