<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

class Workload implements \Countable
{
	private int $count = 0;

	private int $maximum = 0;

	public function CanWork(): bool {
		return $this->count < $this->maximum;
	}

	public function Percent(): float {
		return $this->maximum > 0 ? $this->count / $this->maximum : 1.0;
	}

	public function Maximum(): int {
		return $this->maximum;
	}

	public function count(): int {
		return $this->count;
	}

	public function add(int $count = 1): static {
		$this->count += $count;
		return $this;
	}

	public function setMaximum(int $maximum): static {
		$this->maximum = $maximum;
		return $this;
	}
}
