<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Event\Game\BlownByTheWind;
use Lemuria\Engine\Fantasya\Event\Game\Spawn;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Monster\Bear;
use Lemuria\Model\Fantasya\Commodity\Monster\Goblin;
use Lemuria\Model\Fantasya\Commodity\Monster\Kraken;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Spell\CivilCommotion;
use Lemuria\Model\Fantasya\Spell\EagleEye;
use Lemuria\Model\Fantasya\Spell\InciteMonster;
use Lemuria\Model\Fantasya\Spell\SoundlessShadow;

/**
 * The Timer event adds other events at predefined rounds.
 */
final class Timer extends DelegatedEvent
{
	private const SCHEDULE = [
		55 => [
			['class' => BlownByTheWind::class, 'options' => [BlownByTheWind::REGION => 58, BlownByTheWind::SPELL => SoundlessShadow::class]],
			['class' => BlownByTheWind::class, 'options' => [BlownByTheWind::REGION => 35, BlownByTheWind::SPELL => CivilCommotion::class]],
			['class' => BlownByTheWind::class, 'options' => [BlownByTheWind::REGION => 29, BlownByTheWind::SPELL => EagleEye::class]],
			['class' => BlownByTheWind::class, 'options' => [BlownByTheWind::REGION => 54, BlownByTheWind::SPELL => InciteMonster::class]]
		],
		60 => [
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Type::MONSTER, Spawn::REGION => 14, Spawn::SIZE => 12, Spawn::RACE => Goblin::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Type::MONSTER, Spawn::REGION => 18, Spawn::SIZE =>  2, Spawn::RACE => Kraken::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Type::MONSTER, Spawn::REGION => 21, Spawn::SIZE =>  1, Spawn::RACE => Bear::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Type::MONSTER, Spawn::REGION => 28, Spawn::SIZE => 10, Spawn::RACE => Goblin::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Type::MONSTER, Spawn::REGION => 39, Spawn::SIZE =>  1, Spawn::RACE => Bear::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Type::MONSTER, Spawn::REGION => 50, Spawn::SIZE => 13, Spawn::RACE => Goblin::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Type::MONSTER, Spawn::REGION => 55, Spawn::SIZE =>  1, Spawn::RACE => Kraken::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Type::MONSTER, Spawn::REGION => 60, Spawn::SIZE =>  1, Spawn::RACE => Bear::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Type::MONSTER, Spawn::REGION => 69, Spawn::SIZE =>  8, Spawn::RACE => Goblin::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Type::MONSTER, Spawn::REGION => 78, Spawn::SIZE =>  1, Spawn::RACE => Kraken::class]]
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
