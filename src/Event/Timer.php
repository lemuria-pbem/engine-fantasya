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
use Lemuria\Model\Fantasya\Commodity\Monster\Skeleton;
use Lemuria\Model\Fantasya\Commodity\Monster\Zombie;
use Lemuria\Model\Fantasya\Commodity\Potion\Brainpower;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Commodity\Weapon\Sword;
use Lemuria\Model\Fantasya\Race\Human;
use Lemuria\Model\Fantasya\Spell\Farsight;

/**
 * The Timer event adds other events at predefined rounds.
 */
final class Timer extends DelegatedEvent
{
	/**
	 * @type array<int, array<array>>
	 */
	private const array SCHEDULE = [
		130 => [
			['class' => Drought::class, 'options' => [Drought::RATE => 0.476]],
			['class' => GoblinPlague::class, 'options' => [GoblinPlague::REGION => '', GoblinPlague::DURATION => 3]],
			['class' => CarriedOffWayfarer::class, 'options' => [
				CarriedOffWayfarer::REGION => '', CarriedOffWayfarer::RACE => Human::class,
				CarriedOffWayfarer::INVENTORY => [Silver::class => 30, Sword::class => 1]
			]],
			['class' => BlownByTheWind::class, 'options' => [BlownByTheWind::REGION => '', BlownByTheWind::SPELL => Farsight::class]],
			['class' => PotionGift::class, 'options' => [PotionGift::UNIT => '', PotionGift::POTION => Brainpower::class]]
		],
		140 => [
			['class' => ResetGatherUnits::class, 'options' => [ResetGatherUnits::PARTY => '', ResetGatherUnits::IS_LOOTING => false]]
		],
		144 => [
			['class' => FindWallet::class, 'options' => [FindWallet::UNIT => '', FindWallet::SILVER => 300]]
		],
		145 => [
			// 2, 8, c keine Geschenke
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => '12o']],  // 3
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => '9']],    // 4
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 'zp']],   // 5
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 'gls']],  // 7
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 'cod1']], // 9
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => '2kd']],  // b
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => '3on']],  // cala
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 'gna']],  // d
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 'ause']], // lem
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 'elem']], // mw
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => '231']]   // renn
		],
		154 => [
			['class' => Spawn::class, 'options' => [Spawn::REGION => '', Spawn::SIZE => 1, Spawn::RACE => Zombie::class]],
			// Vorbereitungen für den Nekromanten-NPC
			['class' => Spawn::class, 'options' => [Spawn::PARTY => 'm', Spawn::REGION => '2gn', Spawn::SIZE => 78, Spawn::RACE => Skeleton::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => 'm', Spawn::REGION => '2go', Spawn::SIZE => 85, Spawn::RACE => Skeleton::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => 'm', Spawn::REGION => '2er', Spawn::SIZE => 82, Spawn::RACE => Skeleton::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => 'm', Spawn::REGION => '2eq', Spawn::SIZE => 100, Spawn::RACE => Skeleton::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Spawn::ZOMBIES, Spawn::REGION => '2gn', Spawn::SIZE => 30, Spawn::RACE => Zombie::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Spawn::ZOMBIES, Spawn::REGION => '2go', Spawn::SIZE => 30, Spawn::RACE => Zombie::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Spawn::ZOMBIES, Spawn::REGION => '2er', Spawn::SIZE => 30, Spawn::RACE => Zombie::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Spawn::ZOMBIES, Spawn::REGION => '2eq', Spawn::SIZE => 50, Spawn::RACE => Zombie::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Spawn::ZOMBIES, Spawn::REGION => '2eq', Spawn::SIZE => 50, Spawn::RACE => Zombie::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Spawn::ZOMBIES, Spawn::REGION => '2eq', Spawn::SIZE => 50, Spawn::RACE => Zombie::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Spawn::ZOMBIES, Spawn::REGION => '2il', Spawn::SIZE => 27, Spawn::RACE => Zombie::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Spawn::ZOMBIES, Spawn::REGION => '2im', Spawn::SIZE => 13, Spawn::RACE => Zombie::class]],
			['class' => Spawn::class, 'options' => [Spawn::PARTY => Spawn::ZOMBIES, Spawn::REGION => '2gp', Spawn::SIZE => 33, Spawn::RACE => Zombie::class]]
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
