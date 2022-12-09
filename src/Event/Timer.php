<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Event\Game\BrokenCarriage;
use Lemuria\Engine\Fantasya\Event\Game\HatchGriffinEgg;
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
			['class' => Spawn::class, 'options' => [Spawn::TYPE => Type::Monster, Spawn::REGION => 60, Spawn::SIZE => 7, Spawn::RACE => Wolf::class]],

			['class' => PopulateContinent::class, 'options' => [PopulateContinent::CONTINENT => 2, PopulateContinent::CHANCES => [Wolf::class => 7]]],

			['class' => BrokenCarriage::class, 'options' => [BrokenCarriage::PARTY => 3, BrokenCarriage::REGION => 3689, BrokenCarriage::CARGO => [Griffinegg::class => 3]]],
			['class' => BrokenCarriage::class, 'options' => [BrokenCarriage::PARTY => 6, BrokenCarriage::REGION => 4576, BrokenCarriage::CARGO => [Griffinegg::class => 3]]],
			['class' => BrokenCarriage::class, 'options' => [BrokenCarriage::PARTY => 8, BrokenCarriage::REGION => 1617, BrokenCarriage::CARGO => [Griffinegg::class => 3]]]
		],
		91 => [
			['class' => BrokenCarriage::class, 'options' => [BrokenCarriage::PARTY => 3, BrokenCarriage::REGION => 3689]],
			['class' => BrokenCarriage::class, 'options' => [BrokenCarriage::PARTY => 6, BrokenCarriage::REGION => 4576]],
			['class' => BrokenCarriage::class, 'options' => [BrokenCarriage::PARTY => 8, BrokenCarriage::REGION => 1617]]
		],
		93 => [
			//['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 84]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 193]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 875]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 4]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 5]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 959]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 778594]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 1291]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 1054]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 812295]]
		],
		95 => [['class' => HatchGriffinEgg::class]],
		97 => [['class' => HatchGriffinEgg::class]],
		98 => [['class' => HatchGriffinEgg::class]]
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
