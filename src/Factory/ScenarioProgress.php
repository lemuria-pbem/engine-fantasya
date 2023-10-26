<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Event;
use Lemuria\Engine\Fantasya\Event\Conduct;
use Lemuria\Engine\Fantasya\Event\DelegatedEvent;
use Lemuria\Engine\Fantasya\Event\Finish;
use Lemuria\Engine\Fantasya\Event\Monster;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;

class ScenarioProgress extends DefaultProgress
{
	public function __construct(State $state) {
		parent::__construct($state);
	}

	public function insertScenario(Event $event): static {
		$insert = [Priority::Before->name => [], Priority::Middle->name => [], Priority::After->name => []];
		if ($event instanceof DelegatedEvent) {
			foreach ($event->getDelegates() as $event) {
				$insert[$event->Priority()->name][] = $event;
			}
		} else {
			$insert[$event->Priority()->name][] = $event;
		}

		$events = $this->getEvents();
		$this->clear();
		foreach ($events as $event) {
			if ($event instanceof Monster) {
				$this->insertPriority($insert[Priority::Before->name], $event);
			} elseif ($event instanceof Conduct) {
				$this->insertPriority($insert[Priority::Middle->name], $event);
			} elseif ($event instanceof Finish) {
				$this->insertPriority($insert[Priority::After->name], $event);
			} else {
				$this->add($event);
			}
		}
		return $this;
	}

	/**
	 * @param array<Event> $events
	 */
	protected function insertPriority(array $events, Event $last): void {
		foreach ($events as $event) {
			$this->add($event);
		}
		$this->add($last);
	}
}
