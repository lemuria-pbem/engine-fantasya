<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour\Monster;

use Lemuria\Engine\Fantasya\Event\Behaviour\AbstractBehaviour;

class Zombie extends AbstractBehaviour
{
	public function prepare(): static {
		return $this->seek()->attack();
	}

	public function conduct(): static {
		return $this->roamOrAttack();
	}
}
