<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour;

use function Lemuria\getClass;
use function Lemuria\randChance;
use Lemuria\Engine\Fantasya\Effect\RoamEffect;
use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\Act\Attack;
use Lemuria\Engine\Fantasya\Event\Act\Create;
use Lemuria\Engine\Fantasya\Event\Act\Guard;
use Lemuria\Engine\Fantasya\Event\Act\Home;
use Lemuria\Engine\Fantasya\Event\Act\PickPocket;
use Lemuria\Engine\Fantasya\Event\Act\Roam;
use Lemuria\Engine\Fantasya\Event\Act\Seek;
use Lemuria\Engine\Fantasya\Event\Act\Watch;
use Lemuria\Engine\Fantasya\Event\Behaviour;
use Lemuria\Engine\Fantasya\Event\Reproduction;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\GuardMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Monster;
use Lemuria\Model\Fantasya\Unit;

abstract class AbstractBehaviour implements Behaviour
{
	use BuilderTrait;
	use MessageTrait;

	protected ?Act $guard = null;

	protected ?Act $act = null;

	protected ?Act $roam = null;

	public function __construct(protected Unit $unit) {
	}

	public function Unit(): Unit {
		return $this->unit;
	}

	public function Reproduction(): Reproduction {
		return new Reproduction($this->race());
	}

	public function prepare(): Behaviour {
		return $this;
	}

	public function conduct(): Behaviour {
		return $this;
	}

	public function finish(): Behaviour {
		if ($this->guard instanceof Guard) {
			if ($this->guard->IsGuarding()) {
				$this->unit->setIsGuarding(true);
				$this->message(GuardMessage::class, $this->unit);
			}
		}
		return $this;
	}

	protected function guard(): AbstractBehaviour {
		$guard       = new Guard($this);
		$this->guard = $guard->act();
		return $this;
	}

	protected function watch(): AbstractBehaviour {
		$watch       = new Watch($this);
		$this->guard = $watch->act();
		return $this;
	}

	protected function seek(): AbstractBehaviour {
		$seek      = new Seek($this);
		$this->act = $seek->act();
		return $this;
	}

	protected function roam(bool $leave = false): AbstractBehaviour {
		if ($this->unit->Size() > 0) {
			$this->roam = new Roam($this);
			$this->roam->setLeave($leave)->act();
		}
		return $this;
	}

	protected function home(): AbstractBehaviour {
		if ($this->unit->Size() > 0) {
			$home = new Home($this);
			$home->act();
		}
		return $this;

	}

	protected function attack(): AbstractBehaviour {
		if ($this->act instanceof Seek) {
			$enemy = $this->act->Enemy();
			if (!$enemy->isEmpty()) {
				$attack = new Attack($this);
				$attack->setEnemy($enemy)->act();
			}
		}
		return $this;
	}

	protected function roamOrGuard(): AbstractBehaviour {
		if (!$this->hasRoamEffect()) {
			if (!($this->guard instanceof Guard) || !$this->guard->IsGuarding()) {
				$this->roam();
			}
		}
		return $this;
	}

	protected function roamOrAttack(): AbstractBehaviour {
		if (!$this->hasRoamEffect()) {
			if ($this->act instanceof Seek) {
				$enemy = $this->act->Enemy();
				if (!$enemy->isEmpty()) {
					return $this;
				}
			}
		}
		return $this->roam();
	}

	protected function pickPocketOrRoam(): AbstractBehaviour {
		$forceRoam = $this->hasRoamEffect();
		if (!$forceRoam) {
			if ($this->act instanceof Seek) {
				$enemy = $this->act->Enemy();
				if (!$enemy->isEmpty()) {
					$attack = new PickPocket($this);
					$attack->setEnemy($enemy)->act();
					return $this;
				}
			}
		}
		return $this->roam($forceRoam);
	}

	protected function reproduce(): AbstractBehaviour {
		$reproduction = $this->Reproduction();
		if (randChance($reproduction->Chance())) {
			$create = new Create($this->unit->Party(), $this->unit->Region());
			$create->add($reproduction->Gang())->act();
		}
		return $this;
	}

	protected function reproduceAndLeaveOrRoam(): AbstractBehaviour {
		$reproduction = $this->Reproduction();
		if (randChance($reproduction->Chance())) {
			$create = new Create($this->unit->Party(), $this->unit->Region());
			$create->add($reproduction->Gang())->act();
			return $this->roam(true);
		}
		return $this->roam();
	}

	protected function attackOnWatch(): AbstractBehaviour {
		if ($this->guard instanceof Watch && $this->guard->IsGuarding()) {
			return $this->attack();
		}
		return $this;
	}

	protected function hasRoamEffect(): bool {
		if ($this->unit->Size() > 0) {
			$effect = new RoamEffect(State::getInstance());
			return Lemuria::Score()->find($effect->setUnit($this->unit)) instanceof RoamEffect;
		}
		return false;
	}

	protected function race(): Monster {
		return self::createMonster(getClass($this));
	}
}
