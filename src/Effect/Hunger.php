<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Event\Support;
use Lemuria\Engine\Fantasya\Message\Unit\HungerMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

final class Hunger extends AbstractUnitEffect
{
	use BuilderTrait;

	private const FEED_THRESHOLD = 0.5;

	private const HEALTH_THRESHOLD = 0.9;

	private float $hunger = 1.0;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Middle);
	}

	public function Hunger(): float {
		return $this->hunger;
	}

	public function setHunger(float $hunger): Hunger {
		$this->hunger = $hunger;
		if ($this->Unit()->Health() >= self::HEALTH_THRESHOLD) {
			$this->setNewHealth();
		}
		return $this;
	}

	protected function run(): void {
		$unit = $this->Unit();
		$feed = $this->canBeFed($unit);
		if ($feed < self::FEED_THRESHOLD) {
			$this->setNewHealth(1.0 - $feed);
		}
	}

	private function canBeFed(Unit $unit): float {
		$size = $unit->Size();
		if ($size <= 0) {
			return 1.0;
		}

		$needed  = $size * Support::SILVER;
		$feed    = new Quantity(self::createCommodity(Silver::class), $needed);
		$support = $this->context->getResourcePool($unit)->reserve($unit, $feed);
		return $support->Count() / $needed;
	}

	private function setNewHealth(float $factor = 1.0): void {
		$unit         = $this->Unit();
		$calculus     = new Calculus($unit);
		$hitpoints    = $calculus->hitpoints();
		$hunger       = $factor * $calculus->hunger($unit, $this->hunger);
		$newHitpoints = $unit->Health() * $hitpoints - $hunger;
		$health       = max(0.0, round($newHitpoints / $hitpoints, 2));
		$unit->setHealth($health);
		Lemuria::Log()->debug('Unit ' . $unit . ' takes ' . $hunger . ' damage from hunger.');
		$this->message(HungerMessage::class, $unit)->p($health);
	}
}
