<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour\Monster;

use Lemuria\Engine\Fantasya\Event\Act\Guard;
use Lemuria\Engine\Fantasya\Event\Behaviour\AbstractBehaviour;

class Sandworm extends AbstractBehaviour
{
	public function prepare(): static {
		$this->guard(unguardChance: 1.0);
		if ($this->guard instanceof Guard) {
			if ($this->guard->IsGuarding()) {
				return $this->seek()->attack();
			}
		}
		return $this;
	}

	public function conduct(): static {
		return $this->roamOrGuard();
	}
}
