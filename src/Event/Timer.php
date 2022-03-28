<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Event\Game\BlownByTheWind;
use Lemuria\Engine\Fantasya\Event\Game\FindWallet;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
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
			['class' => FindWallet::class, 'options' => [FindWallet::UNIT => 220, FindWallet::SILVER => 850]],

			['class' => BlownByTheWind::class, 'options' => [BlownByTheWind::REGION => 58, BlownByTheWind::SPELL => SoundlessShadow::class]],
			['class' => BlownByTheWind::class, 'options' => [BlownByTheWind::REGION => 35, BlownByTheWind::SPELL => CivilCommotion::class]],
			['class' => BlownByTheWind::class, 'options' => [BlownByTheWind::REGION => 29, BlownByTheWind::SPELL => EagleEye::class]],
			['class' => BlownByTheWind::class, 'options' => [BlownByTheWind::REGION => 54, BlownByTheWind::SPELL => InciteMonster::class]]
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
