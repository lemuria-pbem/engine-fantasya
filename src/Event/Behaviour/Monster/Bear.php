<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour\Monster;

use Lemuria\Engine\Fantasya\Event\Act\Roam;
use Lemuria\Engine\Fantasya\Event\Behaviour\AbstractBehaviour;

class Bear extends AbstractBehaviour
{
	public function conduct(): Bear {
		$roam = new Roam($this);
		$roam->act();
		return $this;
	}
}
