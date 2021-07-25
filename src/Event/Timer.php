<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Event\Game\BrokenCarriage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Griffinegg;

/**
 * The Timer event adds other events at predefined rounds.
 */
final class Timer extends DelegatedEvent
{
	private const SCHEDULE = [
		20 => [
			['class' => BrokenCarriage::class, 'options' => [BrokenCarriage::PARTY => 4, BrokenCarriage::REGION => 58, BrokenCarriage::CARGO => [Griffinegg::class => 3]]],
			['class' => BrokenCarriage::class, 'options' => [BrokenCarriage::PARTY => 5, BrokenCarriage::REGION => 35, BrokenCarriage::CARGO => [Griffinegg::class => 3]]],
			['class' => BrokenCarriage::class, 'options' => [BrokenCarriage::PARTY => 7, BrokenCarriage::REGION => 29, BrokenCarriage::CARGO => [Griffinegg::class => 3]]],
			['class' => BrokenCarriage::class, 'options' => [BrokenCarriage::PARTY => 824, BrokenCarriage::REGION => 68, BrokenCarriage::CARGO => [Griffinegg::class => 3]]],
			['class' => BrokenCarriage::class, 'options' => [BrokenCarriage::PARTY => 27742, BrokenCarriage::REGION => 54, BrokenCarriage::CARGO => [Griffinegg::class => 3]]]
		],
		21 => [
			['class' => BrokenCarriage::class, 'options' => [BrokenCarriage::PARTY => 4, BrokenCarriage::REGION => 58]],
			['class' => BrokenCarriage::class, 'options' => [BrokenCarriage::PARTY => 5, BrokenCarriage::REGION => 35]],
			['class' => BrokenCarriage::class, 'options' => [BrokenCarriage::PARTY => 7, BrokenCarriage::REGION => 29]],
			['class' => BrokenCarriage::class, 'options' => [BrokenCarriage::PARTY => 824, BrokenCarriage::REGION => 68]],
			['class' => BrokenCarriage::class, 'options' => [BrokenCarriage::PARTY => 27742, BrokenCarriage::REGION => 54]]
		]
	];

	public function __construct(State $state) {
		parent::__construct($state, Action::BEFORE);
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
