<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Event\Support;
use Lemuria\Engine\Fantasya\Factory\Model\HungerMalus;
use Lemuria\Engine\Fantasya\Message\Unit\HungerMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

final class Hunger extends AbstractUnitEffect
{
	use BuilderTrait;

	private const float FEED_THRESHOLD = 0.5;

	private const float HEALTH_THRESHOLD = 0.9;

	private const float MALUS_THRESHOLD = 0.5;

	private const float MINIMUM_HEALTH = 0.15;

	private const float BEG_SILVER = 0.5;

	protected ?bool $isReassign = null;

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

	public function getMalus(Ability $ability): ?HungerMalus {
		return $this->hunger < self::MALUS_THRESHOLD ? null : new HungerMalus($ability);
	}

	public function recover(): self {
		$unit = $this->Unit();
		if ($unit->Health() <= 0.0 && $this->hunger < self::FEED_THRESHOLD) {
			$unit->setHealth(PHP_FLOAT_MIN);
		}
		if ($this->hunger <= 0.0) {
			Lemuria::Score()->remove($this);
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
		$health       = $unit->Health();
		$newHitpoints = $health * $hitpoints - $hunger;
		$newHealth    = round($newHitpoints / $hitpoints, 2);
		if ($newHealth < self::MINIMUM_HEALTH) {
			if ($this->canBegFromPeasants($unit)) {
				$newHealth = self::MINIMUM_HEALTH;
			} else {
				$newHealth = max(0.0, $newHealth);
			}
		}
		if ($newHealth < $health) {
			$unit->setHealth($newHealth);
			$hunger = (int)floor($health * $hitpoints - $newHealth * $hitpoints);
			Lemuria::Log()->debug('Unit ' . $unit . ' takes ' . $hunger . ' damage from hunger.');
			$this->message(HungerMessage::class, $unit)->p($newHealth);
		}
	}

	private function canBegFromPeasants(Unit $unit): bool {
		$need      = (int)ceil($this->Unit()->Size() * self::BEG_SILVER * Support::SILVER);
		$resources = $unit->Region()->Resources();
		$available = $resources[Silver::class]->Count();
		if ($available > 0) {
			$silver = new Quantity(self::createCommodity(Silver::class), min($available, $need));
			$resources->remove($silver);
			Lemuria::Log()->debug('Unit ' . $unit . ' scrounges ' . $silver . ' from the peasants.');
			return true;
		}
		return false;
	}
}
