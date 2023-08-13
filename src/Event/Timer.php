<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Event\Game\FindWallet;
use Lemuria\Engine\Fantasya\Event\Game\GoblinPlague;
use Lemuria\Engine\Fantasya\Event\Game\Spawn;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Monster\Zombie;

/**
 * The Timer event adds other events at predefined rounds.
 */
final class Timer extends DelegatedEvent
{
	private const SCHEDULE = [
		111 => [
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Spawn::ZOMBIES, Spawn::REGION => 2359, Spawn::SIZE => 27, Spawn::RACE => Zombie::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Spawn::ZOMBIES, Spawn::REGION => 3877, Spawn::SIZE => 32, Spawn::RACE => Zombie::class]],
			['class' => FindWallet::class, 'options' => [FindWallet::UNIT => 1669, FindWallet::SILVER => 400]],
			['class' => FindWallet::class, 'options' => [FindWallet::UNIT => 2158, FindWallet::SILVER => 180]],
			['class' => FindWallet::class, 'options' => [FindWallet::UNIT => 4411, FindWallet::SILVER => 250]],
			['class' => GoblinPlague::class, 'options' => [GoblinPlague::REGION => 61, GoblinPlague::DURATION => 2]]
		],
		127 => [
			['class' => FindWallet::class, 'options' => [FindWallet::UNIT => 3828, FindWallet::SILVER => 1200]],
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
