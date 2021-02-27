<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Factory;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Event;
use Lemuria\Engine\Lemuria\Event\Decease;
use Lemuria\Engine\Lemuria\Event\Liquidation;
use Lemuria\Engine\Lemuria\Event\Support;
use Lemuria\Engine\Lemuria\Event\Upkeep;
use Lemuria\Engine\Lemuria\Progress;
use Lemuria\Engine\Lemuria\State;

class DefaultProgress implements Progress
{
	public const EVENTS = [
		// before
		// middle
		Upkeep::class,
		// after
		Support::class, Decease::class, Liquidation::class
	];

	private array $events = [];

	private int $index = 0;

	private int $count;

	#[Pure] public function __construct(State $state) {
		foreach (self::EVENTS as $event) {
			$this->events[] = new $event($state);
		}
		$this->count = count($this->events);
	}

	public function current(): Event {
		return $this->events[$this->index];
	}

	public function next(): void {
		$this->index++;
	}

	public function key(): int {
		return $this->index;
	}

	public function valid(): bool {
		return $this->index < $this->count;
	}

	public function rewind(): void {
		$this->index = 0;
	}

	/**
	 * Add an Event.
	 *
	 * @param Event $event
	 * @return Progress
	 */
	public function add(Event $event): Progress {
		$this->events[] = $event;
		$this->count++;
		return $this;
	}
}
