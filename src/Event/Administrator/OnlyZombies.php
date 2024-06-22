<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Monster\Zombie;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Party\Type;

/**
 * This event is a one-time fix for skeletons that were not moved from the zombies' party.
 */
final class OnlyZombies extends AbstractEvent
{
	use BuilderTrait;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	protected function run(): void {
		$migrants = [];
		$zombie   = self::createMonster(Zombie::class);
		$finder   = $this->state->getTurnOptions()->Finder()->Party();

		$zombies  = $finder->findByRace($zombie);
		$from     = $zombies->People();
		$monsters = $finder->findByType(Type::Monster);
		$to       = $monsters->People();

		foreach ($from as $unit) {
			if ($unit->Race() !== $zombie) {
				$migrants[] = $unit;
			}
		}
		foreach ($migrants as $unit) {
			$from->remove($unit);
			$to->add($unit);
			Lemuria::Log()->debug($unit . ' is moved from zombies to monsters.');
		}
	}
}
