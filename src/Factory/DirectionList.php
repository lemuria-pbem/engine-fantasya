<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\World\Direction;
use Lemuria\Model\World\Way;

class DirectionList implements \Countable
{
	private const string NORTH_EAST = 'NO';

	private const string EAST = 'O';

	private const string SOUTH_EAST = 'SO';

	private const string ROUTE_STOP = 'Pause';

	private readonly CommandFactory $factory;

	private array $directions = [];

	private int $index = 0;

	private int $count = 0;

	private int $numberOfDirections = 0;

	private bool $isRotating = false;

	private int $previousIndex;

	public static function fromWay(Way $way, ?Context $context = null): self {
		if (!$context) {
			$context = new Context(State::getInstance());
		}
		$list = new self($context);
		$way->rewind();
		$way->next();
		while ($way->valid()) {
			$list->add($way->key());
			$way->next();
		}
		return $list;
	}

	public function __construct(Context $context) {
		$this->factory = $context->Factory();
	}

	public function count(): int {
		return $this->isRotating ? PHP_INT_MAX : $this->count;
	}

	public function getNumberOfDirections(): int {
		return $this->numberOfDirections;
	}

	public function hasMore(): bool {
		return $this->index < $this->count;
	}

	public function peek(): Direction {
		if ($this->hasMore()) {
			return $this->directions[$this->index];
		}
		throw new LemuriaException('No more directions.');
	}

	public function next(): Direction {
		if ($this->hasMore()) {
			$this->previousIndex = $this->index;
			$direction           = $this->directions[$this->index++];
			if (!$this->hasMore() && $this->isRotating) {
				$this->index = 0;
			}
			return $direction;
		}
		throw new LemuriaException('No more directions.');
	}

	public function revert(): static {
		$this->index = $this->previousIndex;
		return $this;
	}

	public function set(Phrase $phrase): static {
		$n = $phrase->count();
		for ($i = 1; $i <= $n; $i++) {
			$this->add($phrase->getParameter($i));
		}
		return $this;
	}

	public function add(Direction|string $direction): static {
		if ($this->isRotating && ($direction === Direction::ROUTE_STOP || $this->factory->isRouteStop($direction))) {
			$this->directions[] = Direction::ROUTE_STOP;
		} else {
			$this->directions[] = $direction instanceof Direction ? $direction : $this->factory->direction($direction);
			$this->numberOfDirections++;
		}
		$this->count++;
		return $this;
	}

	public function insertNext(Direction|string $direction): static {
		if (is_string($direction)) {
			$direction = $this->factory->direction($direction);
		}
		array_unshift($this->directions, $direction);
		$this->numberOfDirections++;
		$this->count++;
		return $this;
	}

	public function setIsRotating(bool $isRotating = true): static {
		$this->isRotating = $isRotating;
		return $this;
	}

	public function route(): array {
		$route = [];
		$index = $this->index;
		$n     = $this->count;
		for ($i = $index; $i < $n; $i++) {
			$route[] = $this->routeDirection($i);
		}
		if ($this->isRotating && $index > 0) {
			for ($i = 0; $i < $index; $i++) {
				$route[] = $this->routeDirection($i);
			}
		}
		if ($route[0] === self::ROUTE_STOP) {
			array_shift($route);
			$route[] = self::ROUTE_STOP;
		}
		return $route;
	}

	public function rewind(): static {
		$this->index = 0;
		return $this;
	}

	private function routeDirection(int $i): string {
		/** @var Direction $direction */
		$direction = $this->directions[$i];
		return match ($direction) {
			Direction::Northeast  => self::NORTH_EAST,
			Direction::East       => self::EAST,
			Direction::Southeast  => self::SOUTH_EAST,
			Direction::ROUTE_STOP => self::ROUTE_STOP,
			default               => $direction->value
		};
	}
}
