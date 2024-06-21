<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Event\Administrator\ResetGatherUnits;
use Lemuria\Engine\Fantasya\Factory\Model\TimerEvent;
use Lemuria\Engine\Fantasya\Factory\ReflectionTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

/**
 * The Timer event adds other events at predefined rounds.
 */
final class Timer extends DelegatedEvent
{
	use ReflectionTrait;

	private const string EVENT = 'class';

	private const string OPTIONS = 'options';

	/**
	 * @type array<int, array<array>>
	 */
	private const array SCHEDULE = [
		140 => [
			['class' => ResetGatherUnits::class, 'options' => [ResetGatherUnits::PARTY => '', ResetGatherUnits::IS_LOOTING => false]]
		],
	];

	private int $round;

	public function __construct(State $state) {
		$this->round = Lemuria::Calendar()->Round();
		parent::__construct($state, Priority::Before);
	}

	public function add(int $round, TimerEvent $event): static {
		$this->validateGameEventClass($event->class);
		if ($round === $this->round) {
			$this->addDelegate($event->class, $event->options);
		}
		return $this;
	}

	protected function createDelegates(): void {
		if (isset(self::SCHEDULE[$this->round])) {
			Lemuria::Log()->debug('Adding timed events.');
			foreach (self::SCHEDULE[$this->round] as $definition) {
				$class   = $definition[self::EVENT];
				$options = $definition[self::OPTIONS] ?? null;
				$this->addDelegate($class, $options);
			}
		} else {
			Lemuria::Log()->debug('No timed events for this round.');
		}
	}

	private function addDelegate(string $class, ?array $options): void {
		$event = new $class($this->state);
		if ($options) {
			$event->setOptions($options);
		}
		$this->delegates[] = $event;
	}
}
