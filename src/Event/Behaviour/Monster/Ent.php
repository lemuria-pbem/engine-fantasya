<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour\Monster;

use Lemuria\Engine\Fantasya\Event\Act\Guard;
use Lemuria\Engine\Fantasya\Event\Act\Roam;
use Lemuria\Engine\Fantasya\Event\Behaviour\AbstractBehaviour;

class Ent extends AbstractBehaviour
{
	public function conduct(): Ent {
		$guard = new Guard($this);
		$guard->act();
		if (!$this->unit->IsGuarding()) {
			$roam = new Roam($this);
			$roam->act();
		}
		return $this;
	}
}
