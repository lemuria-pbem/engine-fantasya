<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour\Monster;

use Lemuria\Engine\Fantasya\Event\Behaviour;
use Lemuria\Engine\Fantasya\Event\Behaviour\AbstractBehaviour;

class Skeleton extends AbstractBehaviour
{
	public function prepare(): Behaviour {
		return $this->watch()->seek();
	}

	public function conduct(): Behaviour {
		return $this->attack();
	}
}
