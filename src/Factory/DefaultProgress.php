<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Event;
use Lemuria\Engine\Fantasya\Event\Acquaintance;
use Lemuria\Engine\Fantasya\Event\Administrator;
use Lemuria\Engine\Fantasya\Event\Aftercare;
use Lemuria\Engine\Fantasya\Event\Breeding;
use Lemuria\Engine\Fantasya\Event\Conduct;
use Lemuria\Engine\Fantasya\Event\Decease;
use Lemuria\Engine\Fantasya\Event\Drift;
use Lemuria\Engine\Fantasya\Event\Drown;
use Lemuria\Engine\Fantasya\Event\Fauna;
use Lemuria\Engine\Fantasya\Event\Finish;
use Lemuria\Engine\Fantasya\Event\Founder;
use Lemuria\Engine\Fantasya\Event\Game;
use Lemuria\Engine\Fantasya\Event\Griffinegg;
use Lemuria\Engine\Fantasya\Event\Growth;
use Lemuria\Engine\Fantasya\Event\Integrity;
use Lemuria\Engine\Fantasya\Event\Layabout;
use Lemuria\Engine\Fantasya\Event\Liquidation;
use Lemuria\Engine\Fantasya\Event\MarketFee;
use Lemuria\Engine\Fantasya\Event\MarketUpdate;
use Lemuria\Engine\Fantasya\Event\Monster;
use Lemuria\Engine\Fantasya\Event\Obtainment;
use Lemuria\Engine\Fantasya\Event\Population;
use Lemuria\Engine\Fantasya\Event\PortFee;
use Lemuria\Engine\Fantasya\Event\Recreate;
use Lemuria\Engine\Fantasya\Event\Regrow;
use Lemuria\Engine\Fantasya\Event\ResetSiege;
use Lemuria\Engine\Fantasya\Event\Retirement;
use Lemuria\Engine\Fantasya\Event\Statistics;
use Lemuria\Engine\Fantasya\Event\Subsistence;
use Lemuria\Engine\Fantasya\Event\Support;
use Lemuria\Engine\Fantasya\Event\Timer;
use Lemuria\Engine\Fantasya\Event\TransporterCheck;
use Lemuria\Engine\Fantasya\Event\Upkeep;
use Lemuria\Engine\Fantasya\Event\Visit;
use Lemuria\Engine\Fantasya\Progress;
use Lemuria\Engine\Fantasya\State;

class DefaultProgress implements Progress
{
	protected const EVENTS = [
		// before
		Administrator::class, Timer::class, Game::class, Visit::class, Monster::class, MarketFee::class, PortFee::class,
		// middle
		ResetSiege::class, Conduct::class, Upkeep::class, Subsistence::class, Drift::class, Breeding::class,
		TransporterCheck::class,
		// after
		Finish::class, MarketUpdate::class, Founder::class, Integrity::class, Support::class,
		Population::class, Fauna::class, Griffinegg::class, Growth::class, Regrow::class,
		Decease::class, Drown::class, Liquidation::class,
		Obtainment::class, Acquaintance::class, Recreate::class, Layabout::class, Retirement::class,
		Statistics::class, Aftercare::class
	];

	private array $events = [];

	private int $index = 0;

	private int $count = 0;

	public function __construct(State $state) {
		foreach (self::EVENTS as $event) {
			$this->add(new $event($state));
		}
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
	public function add(Event $event): static {
		$this->events[] = $event;
		$this->count++;
		return $this;
	}

	public function clear(): static {
		$this->events = [];
		$this->count  = 0;
		return $this;
	}

	/**
	 * @return array<Event>
	 */
	protected function getEvents(): array {
		return $this->events;
	}
}
