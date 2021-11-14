<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour;

use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\Act\Attack;
use Lemuria\Engine\Fantasya\Event\Act\Guard;
use Lemuria\Engine\Fantasya\Event\Act\Home;
use Lemuria\Engine\Fantasya\Event\Act\PickPocket;
use Lemuria\Engine\Fantasya\Event\Act\Roam;
use Lemuria\Engine\Fantasya\Event\Act\Seek;
use Lemuria\Engine\Fantasya\Event\Act\Watch;
use Lemuria\Engine\Fantasya\Event\Behaviour;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\GuardMessage;
use Lemuria\Model\Fantasya\Unit;

abstract class AbstractBehaviour implements Behaviour
{
	use MessageTrait;

	protected ?Act $act = null;

	public function __construct(protected Unit $unit) {
	}

	public function Unit(): Unit {
		return $this->unit;
	}

	public function prepare(): Behaviour {
		return $this;
	}

	public function conduct(): Behaviour {
		return $this;
	}

	public function finish(): Behaviour {
		if ($this->act instanceof Guard) {
			if ($this->act->IsGuarding()) {
				$this->unit->setIsGuarding(true);
				$this->message(GuardMessage::class, $this->unit);
			}
		}
		return $this;
	}

	protected function guard(): AbstractBehaviour {
		$guard = new Guard($this);
		$this->act = $guard->act();
		return $this;
	}

	protected function watch(): AbstractBehaviour {
		$watch = new Watch($this);
		$this->act = $watch->act();
		return $this;
	}

	protected function seek(): AbstractBehaviour {
		$seek = new Seek($this);
		$this->act = $seek->act();
		return $this;
	}

	protected function roam(): AbstractBehaviour {
		if ($this->unit->Size() > 0) {
			$roam = new Roam($this);
			$roam->act();
		}
		return $this;
	}

	protected function home(): AbstractBehaviour {
		if ($this->unit->Size() > 0) {
			$roam = new Home($this);
			$roam->act();
		}
		return $this;

	}

	protected function attack(): AbstractBehaviour {
		if ($this->act instanceof Seek && $this->act->Enemy()) {
			$attack = new Attack($this);
			$attack->setEnemy($this->act->Enemy())->act();
		}
		return $this;
	}

	protected function roamOrGuard(): AbstractBehaviour {
		if ($this->unit->Size() > 0) {
			if (!($this->act instanceof Guard) || !$this->act->IsGuarding()) {
				$roam = new Roam($this);
				$roam->act();
			}
		}
		return $this;
	}

	protected function roamOrAttack(): AbstractBehaviour {
		if ($this->unit->Size() > 0) {
			if ($this->act instanceof Seek && $this->act->Enemy()) {
				$attack = new Attack($this);
				$attack->setEnemy($this->act->Enemy())->act();
			} else {
				$roam = new Roam($this);
				$roam->act();
			}
		}
		return $this;
	}

	protected function roamOrPickPocket(): AbstractBehaviour {
		if ($this->unit->Size() > 0) {
			if ($this->act instanceof Seek && $this->act->Enemy()) {
				$attack = new PickPocket($this);
				$attack->setEnemy($this->act->Enemy())->act();
			} else {
				$roam = new Roam($this);
				$roam->act();
			}
		}
		return $this;
	}
}
