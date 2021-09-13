<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Event\Game\HatchGriffinEgg;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

/**
 * The Timer event adds other events at predefined rounds.
 */
final class Timer extends DelegatedEvent
{
	private const SCHEDULE = [
		27 => [['class' => HatchGriffinEgg::class]],
		29 => [['class' => HatchGriffinEgg::class]],
		30 => [['class' => HatchGriffinEgg::class]]
	];

	public function __construct(State $state) {
		parent::__construct($state, Action::BEFORE);
	}

	protected function createDelegates(): void {
		$round = Lemuria::Calendar()->Round();
		if (isset(self::SCHEDULE[$round])) {
			Lemuria::Log()->debug('Adding timed events.');
			foreach (self::SCHEDULE[$round] as $definition) {
				$class = $definition['class'];
				$event = new $class($this->state);
				if (isset($definition['options'])) {
					$event->setOptions($definition['options']);
				}
				$this->delegates[] = $event;
			}
		} else {
			Lemuria::Log()->debug('No timed events for this round.');
		}
	}
}
