<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Effect;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Action;
use Lemuria\Engine\Lemuria\Message\Unit\HungerMessage;
use Lemuria\Engine\Lemuria\State;

final class Hunger extends AbstractUnitEffect
{
	private float $hunger = 1.0;

	#[Pure] public function __construct(State $state) {
		parent::__construct($state, Action::BEFORE);
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
		$unit   = $this->Unit();
		$health = $unit->Health();
		if ($health >= 0.9) {
			$hunger = $this->hunger < 0.5 ? 15 : 25;
		} else {
			$hunger = $this->hunger < 0.5 ? 25 : 40;
		}
		$health -= max(0.0, round(rand($hunger - 3, $hunger + 3) / 100, 2));
		$unit->setHealth($health);
		$this->message(HungerMessage::class, $unit)->p($health);
	}
}
