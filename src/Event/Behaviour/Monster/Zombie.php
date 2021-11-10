<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour\Monster;

use Lemuria\Engine\Fantasya\Event\Act\Attack;
use Lemuria\Engine\Fantasya\Event\Act\Seek;
use Lemuria\Engine\Fantasya\Event\Behaviour\AbstractBehaviour;

class Zombie extends AbstractBehaviour
{
	public function conduct(): Zombie {
		$attack = new Attack($this);
		$attack->act(); //TODO react on battle result
		$seek = new Seek($this);
		$seek->act();
		return $this;
	}
}
