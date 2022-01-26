<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Message\Unit\HungerMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

final class Hunger extends AbstractUnitEffect
{
	private float $hunger = 1.0;

	public function __construct(State $state) {
		parent::__construct($state, Priority::BEFORE);
	}

	public function Hunger(): float {
		return $this->hunger;
	}

	public function setHunger(float $hunger): Hunger {
		$this->hunger = $hunger;
		$this->run();
		return $this;
	}

	protected function run(): void {
		$unit         = $this->Unit();
		$calculus     = new Calculus($unit);
		$hitpoints    = $calculus->hitpoints();
		$hunger       = $calculus->hunger($unit, $this->hunger);
		$newHitpoints = $unit->Health() * $hitpoints - $hunger;
		$health       = max(0.0, round($newHitpoints / $hitpoints, 2));
		$unit->setHealth($health);
		Lemuria::Log()->debug('Unit ' . $unit . ' takes ' . $hunger . ' damage from hunger.');
		$this->message(HungerMessage::class, $unit)->p($health);
	}
}
