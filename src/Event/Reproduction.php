<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Model\Fantasya\Gang;
use Lemuria\Model\Fantasya\Monster;

final class Reproduction
{
	private float $chance = 0.0;

	private int $size = 0;

	public function __construct(private Monster $race) {
	}

	public function Chance(): float {
		return $this->chance;
	}

	public function Gang(): Gang {
		return new Gang($this->race, $this->size);
	}

	public function setChance(float $chance): Reproduction {
		$this->chance = $chance;
		return $this;
	}

	public function setSize(int $size): Reproduction {
		$this->size = $size;
		return $this;
	}
}
