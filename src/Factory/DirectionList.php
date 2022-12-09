<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\World\Direction;

class DirectionList implements \Countable
{
	private const NORTH_EAST = 'NO';

	private const EAST = 'O';

	private const SOUTH_EAST = 'SO';

	private const ROUTE_STOP = 'Pause';

	private readonly CommandFactory $factory;

	private array $directions = [];

	private int $index = 0;

	private int $count = 0;

	private bool $isRotating = false;

	private int $previousIndex;

	public function __construct(Context $context) {
		$this->factory = $context->Factory();
	}

	public function count(): int {
		return $this->isRotating ? PHP_INT_MAX : $this->count;
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

	public function revert(): DirectionList {
		$this->index = $this->previousIndex;
		return $this;
	}

	public function set(Phrase $phrase): DirectionList {
		$n = $phrase->count();
		for ($i = 1; $i <= $n; $i++) {
			$this->add($phrase->getParameter($i));
		}
		return $this;
	}

	public function add(string $direction): DirectionList {
		if ($this->isRotating && $this->factory->isRouteStop($direction)) {
			$this->directions[] = Direction::ROUTE_STOP;
		} else {
			$this->directions[] = $this->factory->direction($direction);
		}
		$this->count++;
		return $this;
	}

	public function setIsRotating(bool $isRotating = true): DirectionList {
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
