<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Event\Game\TheWildHunt;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;


/**
 * The Timer event adds other events at predefined rounds.
 */
final class Timer extends DelegatedEvent
{
	private const SCHEDULE = [
		41 => [
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 4]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 5]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 15409]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 1054]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 1050517]]
		]
	];

	public function __construct(State $state) {
		parent::__construct($state, Priority::BEFORE);
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
