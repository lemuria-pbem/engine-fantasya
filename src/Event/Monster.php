<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Monster as MonsterModel;
use Lemuria\Model\Fantasya\Unit;

/**
 * This event prepares the monsters' behaviour.
 */
final class Monster extends AbstractEvent
{
	private const NAMESPACE = 'Lemuria\\Engine\\Fantasya\\Event\\Behaviour\\Monster';

	private static array $behaviours = [];

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	protected function run(): void {
		$count = 0;
		foreach (Lemuria::Catalog()->getAll(Domain::Party) as $party /* @var Party $party */) {
			if ($party->Type() === Type::Monster) {
				foreach ($party->People() as $unit /* @var Unit $unit */) {
					if ($unit->Size() > 0) {
						$race = $unit->Race();
						if ($race instanceof MonsterModel) {
							$behaviourClass = self::getBehaviour($race);
							if ($behaviourClass) {
								$behaviour = new $behaviourClass($unit);
								$this->state->addMonster($behaviour->prepare());
								$count++;
							}
						}
					}
				}
			}
		}
		Lemuria::Log()->debug('Behaviours for ' . $count . ' monster units have been added.');
	}

	private static function getBehaviour(MonsterModel $race): ?string {
		$class = getClass($race);
		if (!array_key_exists($class, self::$behaviours)) {
			$behaviour = self::NAMESPACE . '\\' . $class;
			if (class_exists($behaviour)) {
				self::$behaviours[$class] = $behaviour;
			} else {
				self::$behaviours[$class] = null;
				Lemuria::Log()->debug('Monster ' . $class . ' has no defined behaviour yet.');
			}

		}
		return self::$behaviours[$class];
	}
}
