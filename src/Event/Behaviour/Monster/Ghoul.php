<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour\Monster;

use Lemuria\Engine\Fantasya\Event\Behaviour\AbstractBehaviour;

class Ghoul extends AbstractBehaviour
{
	public function conduct(): static {
		return $this->home();
	}
}
