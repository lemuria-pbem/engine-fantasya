<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Event\Game\BrokenCarriage;
use Lemuria\Engine\Fantasya\Event\Game\GoblinPlague;
use Lemuria\Engine\Fantasya\Event\Game\PopulateContinent;
use Lemuria\Engine\Fantasya\Event\Game\Spawn;
use Lemuria\Engine\Fantasya\Event\Game\TheWildHunt;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Monster\Wolf;
use Lemuria\Model\Fantasya\Party\Type;

/**
 * The Timer event adds other events at predefined rounds.
 */
final class Timer extends DelegatedEvent
{
	private const SCHEDULE = [
		90 => [
			['class' => Spawn::class, 'options' => [Spawn::TYPE => Type::Monster, Spawn::REGION => 39, Spawn::SIZE => 8, Spawn::RACE => Wolf::class]],
			['class' => PopulateContinent::class, 'options' => [PopulateContinent::CONTINENT => 2, PopulateContinent::CHANCES => [Wolf::class => 7]]],
			['class' => BrokenCarriage::class, 'options' => [BrokenCarriage::PARTY => 8, BrokenCarriage::REGION => 1617]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 1573]]
		],
		111 => [
			['class' => GoblinPlague::class, 'options' => [GoblinPlague::REGION => 61, GoblinPlague::DURATION => 2]]
		]
	];

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
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
