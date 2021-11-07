<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Monster as MonsterModel;
use Lemuria\Model\Fantasya\Unit;

/**
 * This event conducts the monsters' behaviour.
 */
final class Monster extends AbstractEvent
{
	private const NAMESPACE = 'Lemuria\\Engine\\Fantasya\\Event\\Behaviour\\Monster';

	private static array $behaviours = [];

	public function __construct(State $state) {
		parent::__construct($state, Action::MIDDLE);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Catalog::PARTIES) as $party /* @var Party $party */) {
			if ($party->Type() === Party::MONSTER) {
				foreach ($party->People() as $unit /* @var Unit $unit */) {
					if ($unit->Size() > 0) {
						$race = $unit->Race();
						if ($race instanceof MonsterModel) {
							$behaviour = self::getBehaviour($race);
							$behaviour?->setUnit($unit)->conduct();
						}
					}
				}
			}
		}
	}

	private static function getBehaviour(MonsterModel $race): ?Behaviour {
		$class = getClass($race);
		if (!array_key_exists($class, self::$behaviours)) {
			$behaviour = self::NAMESPACE . '\\' . $class;
			if (class_exists($behaviour)) {
				self::$behaviours[$class] = new $behaviour();
			} else {
				self::$behaviours[$class] = null;
				Lemuria::Log()->debug('Monster ' . $class . ' has no defined behaviour yet.');
			}

		}
		return self::$behaviours[$class];
	}
}
