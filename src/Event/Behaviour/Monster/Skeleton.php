<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour\Monster;

use Lemuria\Engine\Fantasya\Event\Behaviour\AbstractBehaviour;

class Skeleton extends AbstractBehaviour
{
	public function prepare(): static {
		return $this->watch()->seek()->attackOnWatch();
	}
}
