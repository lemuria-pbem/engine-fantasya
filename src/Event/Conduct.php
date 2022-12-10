<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

/**
 * This event conducts the monsters' behaviour.
 */
final class Conduct extends AbstractEvent
{
	public function __construct(State $state) {
		parent::__construct($state, Priority::Middle);
	}

	protected function run(): void {
		$monsters = $this->state->getAllMonsters();
		Lemuria::Log()->debug('Behaviour of ' . count($monsters) . ' monsters is conducted.');
		foreach ($monsters as $behaviour) {
			$behaviour->conduct();
		}
	}
}
