<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\CountableTrait;
use Lemuria\IteratorTrait;

class Buzzes implements \ArrayAccess, \Countable, \Iterator
{
	use CountableTrait;
	use IteratorTrait;

	/**
	 * @var array<Buzz>
	 */
	private array $list = [];

	/**
	 * @var int $offset
	 */
	public function offsetExists(mixed $offset): bool {
		return isset($this->list[$offset]);
	}

	/**
	 * @var int $offset
	 */
	public function offsetGet(mixed $offset): Buzz {
		if ($this->offsetExists($offset)) {
			return $this->list[$offset];
		}
		throw new \OutOfBoundsException();
	}

	/**
	 * @param int $offset
	 * @param Buzz $value
	 */
	public function offsetSet(mixed $offset, mixed $value): void {
		if ($this->offsetExists($offset)) {
			$this->list[$offset] = $value;
		} else {
			$this->add($value);
		}
	}

	/**
	 * @param int $offset
	 */
	public function offsetUnset(mixed $offset): void {
		if ($this->offsetExists($offset)) {
			unset($this->list[$offset]);
			$this->count--;
			$this->list = array_values($this->list);
		}
	}

	public function current(): Buzz {
		return $this->offsetGet($this->index);
	}

	public function clear(): static {
		$this->list  = [];
		$this->index = 0;
		$this->count = 0;
		return $this;
	}

	public function add(Buzz $buzz): static {
		$this->list[] = $buzz;
		$this->count++;
		return $this;
	}
}
