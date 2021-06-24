<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Event;
use Lemuria\Engine\Fantasya\Event\Acquaintance;
use Lemuria\Engine\Fantasya\Event\Aftercare;
use Lemuria\Engine\Fantasya\Event\Decease;
use Lemuria\Engine\Fantasya\Event\Drift;
use Lemuria\Engine\Fantasya\Event\Fauna;
use Lemuria\Engine\Fantasya\Event\Founder;
use Lemuria\Engine\Fantasya\Event\Growth;
use Lemuria\Engine\Fantasya\Event\Layabout;
use Lemuria\Engine\Fantasya\Event\Liquidation;
use Lemuria\Engine\Fantasya\Event\MarketUpdate;
use Lemuria\Engine\Fantasya\Event\Population;
use Lemuria\Engine\Fantasya\Event\Regrow;
use Lemuria\Engine\Fantasya\Event\Subsistence;
use Lemuria\Engine\Fantasya\Event\Support;
use Lemuria\Engine\Fantasya\Event\Timer;
use Lemuria\Engine\Fantasya\Event\Upkeep;
use Lemuria\Engine\Fantasya\Event\Visit;
use Lemuria\Engine\Fantasya\Progress;
use Lemuria\Engine\Fantasya\State;

class DefaultProgress implements Progress
{
	public const EVENTS = [
		// before
		Visit::class, Timer::class,
		// middle
		Upkeep::class, Subsistence::class, Drift::class,
		// after
		MarketUpdate::class, Founder::class, Support::class, Population::class, Fauna::class, Growth::class,
		Regrow::class, Decease::class, Liquidation::class, Acquaintance::class, Layabout::class, Aftercare::class
	];

	private array $events = [];

	private int $index = 0;

	private int $count;

	public function __construct(State $state) {
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
