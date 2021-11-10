<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\State;

/**
 * This event finishes the monsters' behaviour.
 */
final class Finish extends AbstractEvent
{
	public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
	}

	protected function run(): void {
		foreach ($this->state->getAllMonsters() as $behaviour) {
			$behaviour->finish();
		}
	}
}
