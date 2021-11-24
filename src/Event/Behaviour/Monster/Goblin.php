<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour\Monster;

use Lemuria\Engine\Fantasya\Event\Behaviour;
use Lemuria\Engine\Fantasya\Event\Behaviour\AbstractBehaviour;

class Goblin extends AbstractBehaviour
{
	public function prepare(): Behaviour {
		if ($this->hasRoamEffect()) {
			return $this;
		}
		return $this->seek();
	}

	public function conduct(): Behaviour {
		return $this->roamOrPickPocket();
	}
}
