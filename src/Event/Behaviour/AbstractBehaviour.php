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
use Lemuria\Engine\Fantasya\Event\Act\Hibernate;
use Lemuria\Engine\Fantasya\Event\Act\Home;
use Lemuria\Engine\Fantasya\Event\Act\Perish;
use Lemuria\Engine\Fantasya\Event\Act\PickPocket;
use Lemuria\Engine\Fantasya\Event\Act\Prey;
use Lemuria\Engine\Fantasya\Event\Act\Roam;
use Lemuria\Engine\Fantasya\Event\Act\Scatter;
use Lemuria\Engine\Fantasya\Event\Act\Seek;
use Lemuria\Engine\Fantasya\Event\Act\Watch;
use Lemuria\Engine\Fantasya\Event\Behaviour;
use Lemuria\Engine\Fantasya\Event\Reproduction;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\GuardMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Gang;
use Lemuria\Model\Fantasya\Monster;
use Lemuria\Model\Fantasya\Unit;

abstract class AbstractBehaviour implements Behaviour
{
	use BuilderTrait;
	use MessageTrait;

	protected ?Act $guard = null;

	protected ?Act $act = null;

	protected ?Act $roam = null;

	protected ?Act $perish = null;

	protected ?Act $hibernate = null;

	public function __construct(protected Unit $unit) {
	}

	public function Unit(): Unit {
		return $this->unit;
	}

	public function Reproduction(): Reproduction {
		return new Reproduction($this->unit);
	}

	public function prepare(): static {
		return $this;
	}

	public function conduct(): static {
		return $this;
	}

	public function finish(): static {
		if ($this->roam instanceof Roam) {
			$this->roam->act();
		}
		if ($this->guard instanceof Guard) {
			if ($this->guard->IsGuarding()) {
				$this->unit->setIsGuarding(true);
				$this->message(GuardMessage::class, $this->unit);
			}
		}
		return $this;
	}

	protected function hibernate(): static {
		$this->hibernate = new Hibernate($this);
		$this->hibernate->act();
		return $this;
	}

	protected function guard(float $guardChance = 0.5, float $unguardChance = 0.2): static {
		if (!$this->perish) {
			$guard       = new Guard($this);
			$this->guard = $guard->guard($guardChance)->unguard($unguardChance)->act();
		}
		return $this;
	}

	protected function watch(): static {
		if (!$this->perish) {
			$watch       = new Watch($this);
			$this->guard = $watch->act();
		}
		return $this;
	}

	protected function seek(): static {
		if (!$this->perish) {
			$seek      = new Seek($this);
			$this->act = $seek->act();
		}
		return $this;
	}

	protected function seekUnperceptive(int $minimumPerception = 0, float $degradation = 0.5): static {
		if (!$this->perish) {
			$seek      = new Seek($this);
			$this->act = $seek->setPerceptionChance($minimumPerception, $degradation)->act();
		}
		return $this;
	}

	protected function prey(): static {
		if (!$this->perish) {
			$prey      = new Prey($this);
			$this->act = $prey->act();
		}
		return $this;
	}

	protected function roam(bool $leave = false): static {
		if (!$this->perish && $this->unit->Size() > 0) {
			$this->roam = new Roam($this);
			$this->roam->setLeave($leave);
		}
		return $this;
	}

	protected function home(): static {
		if (!$this->perish && $this->unit->Size() > 0) {
			$home = new Home($this);
			$home->act();
		}
		return $this;

	}

	protected function lurk(): static {
		if (!$this->perish && $this->unit->Size() > 0) {
			$home = new Home($this);
			$home->act();
		}
		return $this;

	}

	protected function attack(): static {
		if (!$this->perish && $this->act instanceof Seek) {
			$enemy = $this->act->Enemy();
			if (!$enemy->isEmpty()) {
				$attack = new Attack($this);
				$attack->setEnemy($enemy)->act();
			}
		}
		return $this;
	}

	protected function roamOrGuard(): static {
		if (!$this->hasRoamEffect()) {
			if (!($this->guard instanceof Guard) || !$this->guard->IsGuarding()) {
				$this->roam();
			}
		}
		return $this;
	}

	protected function roamOrAttack(): static {
		if (!$this->hasRoamEffect()) {
			if (!$this->perish && $this->act instanceof Seek) {
				$enemy = $this->act->Enemy();
				if (!$enemy->isEmpty()) {
					return $this;
				}
			}
		}
		return $this->roam();
	}

	protected function pickPocketOrRoam(): static {
		$forceRoam = $this->hasRoamEffect();
		if (!$this->perish && !$forceRoam) {
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

	protected function reproduce(): static {
		$reproduction = $this->Reproduction();
		if (!$this->perish && randChance($reproduction->Chance())) {
			$create = new Create($this->unit->Party(), $this->unit->Region());
			$create->add($reproduction->Gang())->act();
		}
		return $this;
	}

	protected function reproduceAndLeaveOrRoam(): static {
		$reproduction = $this->Reproduction();
		if (!$this->perish && randChance($reproduction->Chance())) {
			$create = new Create($this->unit->Party(), $this->unit->Region());
			$create->add($reproduction->Gang())->act();
			return $this->roam(true);
		}
		return $this->roam();
	}

	protected function scatter(int $minUnits = 2, int $minPersons = PHP_INT_MAX): static {
		if (!$this->perish) {
			$scatter = new Scatter($this);
			$scatter->setUnits($minUnits)->setPersons($minPersons)->act();
		}
		return $this;
	}

	protected function split(int $maxPersons): static {
		$size = $this->unit->Size();
		if ($size > 1 && $size > $maxPersons) {
			$split = (int)floor($size / 2);
			$this->unit->setSize($size - $split);
			$this->act = new Create($this->unit->Party(), $this->unit->Region());
			$this->act->add(new Gang($this->unit->Race(), $split))->act();
		}
		return $this;
	}

	protected function attackOnWatch(): static {
		if ($this->guard instanceof Watch && $this->guard->IsGuarding()) {
			return $this->attack();
		}
		return $this;
	}

	protected function perishByChance(float $chance): static {
		if (randChance($chance)) {
			$perish = new Perish($this);
			$perish->act();
			if ($this->unit->Health() <= 0.0) {
				$this->perish = $perish;
			}
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
