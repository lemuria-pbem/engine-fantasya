<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Event\Administrator\ResetGatherUnits;
use Lemuria\Engine\Fantasya\Event\Game\BlownByTheWind;
use Lemuria\Engine\Fantasya\Event\Game\CarriedOffWayfarer;
use Lemuria\Engine\Fantasya\Event\Game\Drought;
use Lemuria\Engine\Fantasya\Event\Game\FindWallet;
use Lemuria\Engine\Fantasya\Event\Game\GoblinPlague;
use Lemuria\Engine\Fantasya\Event\Game\PotionGift;
use Lemuria\Engine\Fantasya\Event\Game\Spawn;
use Lemuria\Engine\Fantasya\Event\Game\TheWildHunt;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Monster\Zombie;
use Lemuria\Model\Fantasya\Commodity\Potion\Brainpower;
use Lemuria\Model\Fantasya\Commodity\Potion\DrinkOfCreation;
use Lemuria\Model\Fantasya\Commodity\Potion\ElixirOfPower;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Commodity\Weapon\Sword;
use Lemuria\Model\Fantasya\Race\Human;
use Lemuria\Model\Fantasya\Spell\AstralPassage;
use Lemuria\Model\Fantasya\Spell\Farsight;
use Lemuria\Model\Fantasya\Spell\GazeOfTheGriffin;

/**
 * The Timer event adds other events at predefined rounds.
 */
final class Timer extends DelegatedEvent
{
	private const SCHEDULE = [
		130 => [
			['class' => Drought::class, 'options' => [Drought::RATE => 0.476]],
			['class' => GoblinPlague::class, 'options' => [GoblinPlague::REGION => 3760, GoblinPlague::DURATION => 3]],
			['class' => CarriedOffWayfarer::class, 'options' => [
				CarriedOffWayfarer::REGION => 3949, CarriedOffWayfarer::RACE => Human::class,
				CarriedOffWayfarer::INVENTORY => [Silver::class => 30, Sword::class => 1]
			]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Spawn::ZOMBIES, Spawn::REGION => 3949, Spawn::SIZE => 1, Spawn::RACE => Zombie::class]],
			['class' => BlownByTheWind::class, 'options' => [BlownByTheWind::REGION => 526, BlownByTheWind::SPELL => Farsight::class]],
			['class' => PotionGift::class, 'options' => [PotionGift::UNIT => 3828, PotionGift::POTION => Brainpower::class]]
		],
		140 => [
			['class' => ResetGatherUnits::class, 'options' => [ResetGatherUnits::PARTY => 5, ResetGatherUnits::IS_LOOTING => false]]
		],
		141 => [
			['class' => FindWallet::class, 'options' => [FindWallet::UNIT => 2856, FindWallet::SILVER => 2400]]
		],
		145 => [
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 1392]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 9]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 1285]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 21520]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 548735]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 3325]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 21574]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 4775]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 506462]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 680926]],
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 2701]]
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
