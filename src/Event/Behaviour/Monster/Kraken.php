<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour\Monster;

use Lemuria\Engine\Fantasya\Event\Behaviour;
use Lemuria\Engine\Fantasya\Event\Behaviour\AbstractBehaviour;

class Kraken extends AbstractBehaviour
{
	public function conduct(): Behaviour {
		return $this->roam();
	}
}
