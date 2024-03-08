<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Event\Administrator\ResetGatherUnits;
use Lemuria\Engine\Fantasya\Event\Game\BlownByTheWind;
use Lemuria\Engine\Fantasya\Event\Game\CarriedOffWayfarer;
use Lemuria\Engine\Fantasya\Event\Game\Drought;
use Lemuria\Engine\Fantasya\Event\Game\FindWallet;
use Lemuria\Engine\Fantasya\Event\Game\GoblinPlague;
use Lemuria\Engine\Fantasya\Event\Game\PopulateContinent;
use Lemuria\Engine\Fantasya\Event\Game\PotionGift;
use Lemuria\Engine\Fantasya\Event\Game\Spawn;
use Lemuria\Engine\Fantasya\Event\Game\TheWildHunt;
use Lemuria\Engine\Fantasya\Event\Game\TransportMonster;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Monster\Ent;
use Lemuria\Model\Fantasya\Commodity\Monster\Ghoul;
use Lemuria\Model\Fantasya\Commodity\Monster\Sandworm;
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
		145 => [
			// 2, 8, b, c keine Geschenke
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => '12o']],  // 3
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => '9']],    // 4
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 'zp']],   // 5
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 'gls']],  // 7
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 'cod1']], // 9
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => '3on']],  // cala
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 'gna']],  // d
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 'ause']], // lem
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => 'elem']], // mw
			['class' => TheWildHunt::class, 'options' => [TheWildHunt::UNIT => '231']]   // renn
		],
		155 => [
			['class' => Drought::class, 'options' => [Drought::RATE => 0.35]],
			['class' => PopulateContinent::class, 'options' => [PopulateContinent::CONTINENT => 1, PopulateContinent::CHANCES => [Ent::class => 35, Ghoul::class => 30]]],
			['class' => Spawn::class, 'options' => [Spawn::REGION => 'ya', Spawn::SIZE => 1, Spawn::RACE => Sandworm::class]],
		],
		156 => [
			['class' => FindWallet::class, 'options' => [FindWallet::UNIT => 'af', FindWallet::SILVER => 100]],
			['class' => TransportMonster::class, 'options' => [TransportMonster::UNIT => '2ib', TransportMonster::REGION => '2cx']],
			['class' => TransportMonster::class, 'options' => [TransportMonster::UNIT => '2o4', TransportMonster::REGION => '2gr']],
			['class' => TransportMonster::class, 'options' => [TransportMonster::UNIT => '4iz', TransportMonster::REGION => '2gq']],
			['class' => TransportMonster::class, 'options' => [TransportMonster::UNIT => '2uk', TransportMonster::REGION => '2in']],
			['class' => TransportMonster::class, 'options' => [TransportMonster::UNIT => '2xt', TransportMonster::REGION => '2in']],
			['class' => TransportMonster::class, 'options' => [TransportMonster::UNIT => '2vu', TransportMonster::REGION => '2il']]
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
