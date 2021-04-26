<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\World;

class DirectionList implements \Countable
{
	public const ROUTE_STOP = 'Pause';

	private CommandFactory $factory;

	private array $directions = [];

	private int $index = 0;

	private int $count = 0;

	private bool $isRotating = false;

	#[Pure] public function __construct(Context $context) {
		$this->factory = $context->Factory();
	}

	public function count(): int {
		return $this->isRotating ? PHP_INT_MAX : $this->count;
	}

	public function hasMore(): bool {
		return $this->index < $this->count;
	}

	public function next(): string {
		if ($this->hasMore()) {
			$direction = $this->directions[$this->index++];
			if (!$this->hasMore() && $this->isRotating) {
				$this->index = 0;
			}
			return $direction;
		}
		throw new LemuriaException('No more directions.');
	}

	public function add(Phrase $phrase): DirectionList {
		$n = $phrase->count();
		for ($i = 1; $i <= $n; $i++) {
			$this->addDirection($phrase->getParameter($i));
		}
		$this->count = count($this->directions);
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

	private function addDirection(string $direction): void {
		if ($this->isRotating && $this->factory->isRouteStop($direction)) {
			$this->directions[] = self::ROUTE_STOP;
		} else {
			$this->directions[] = $this->factory->direction($direction);
		}
	}

	private function routeDirection(int $i): string {
		$direction = $this->directions[$i];
		return match ($direction) {
			World::NORTHEAST => 'NO',
			World::EAST      => 'O',
			World::SOUTHEAST => 'SO',
			default          => $direction
		};
	}
}
