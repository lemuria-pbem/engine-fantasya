<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Event\Game\Spawn;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Monster\Bear;
use Lemuria\Model\Fantasya\Commodity\Monster\Ent;
use Lemuria\Model\Fantasya\Commodity\Monster\Ghoul;
use Lemuria\Model\Fantasya\Commodity\Monster\Goblin;
use Lemuria\Model\Fantasya\Commodity\Monster\Kraken;
use Lemuria\Model\Fantasya\Commodity\Monster\Skeleton;
use Lemuria\Model\Fantasya\Party;

/**
 * The Timer event adds other events at predefined rounds.
 */
final class Timer extends DelegatedEvent
{
	private const SCHEDULE = [
		35 => [
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 14, Spawn::SIZE => 13, Spawn::RACE => Goblin::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 18, Spawn::SIZE =>  1, Spawn::RACE => Kraken::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 18, Spawn::SIZE =>  2, Spawn::RACE => Kraken::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 21, Spawn::SIZE =>  1, Spawn::RACE => Bear::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 22, Spawn::SIZE =>  9, Spawn::RACE => Ghoul::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 23, Spawn::SIZE =>  1, Spawn::RACE => Bear::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 27, Spawn::SIZE =>  1, Spawn::RACE => Bear::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 28, Spawn::SIZE =>  9, Spawn::RACE => Goblin::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 39, Spawn::SIZE =>  1, Spawn::RACE => Bear::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 46, Spawn::SIZE => 12, Spawn::RACE => Goblin::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 47, Spawn::SIZE =>  1, Spawn::RACE => Bear::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 50, Spawn::SIZE => 17, Spawn::RACE => Goblin::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 55, Spawn::SIZE =>  1, Spawn::RACE => Kraken::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 59, Spawn::SIZE =>  1, Spawn::RACE => Skeleton::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 60, Spawn::SIZE =>  1, Spawn::RACE => Bear::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 67, Spawn::SIZE =>  4, Spawn::RACE => Ghoul::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 68, Spawn::SIZE =>  5, Spawn::RACE => Ent::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 69, Spawn::SIZE => 14, Spawn::RACE => Goblin::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 74, Spawn::SIZE => 22, Spawn::RACE => Skeleton::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 78, Spawn::SIZE =>  1, Spawn::RACE => Kraken::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Party::MONSTER, Spawn::REGION => 79, Spawn::SIZE => 13, Spawn::RACE => Ghoul::class]],
		]
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
