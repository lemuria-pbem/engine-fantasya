<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Effect\Hunger;
use Lemuria\Engine\Fantasya\Factory\MagicTrait;
use Lemuria\Engine\Fantasya\Message\Unit\RecreateAuraMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RecreateHealthMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Building\Tavern;
use Lemuria\Model\Fantasya\Monster;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Unit;

/**
 * Units regain health and aura.
 */
final class Recreate extends AbstractEvent
{
	use MagicTrait;

	public function __construct(State $state) {
		parent::__construct($state, Priority::AFTER);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Domain::UNIT) as $unit /* @var Unit $unit */) {
			$type = $unit->Party()->Type();
			switch ($type) {
				case Type::PLAYER :
					if (!$this->hasHunger($unit)) {
						if ($unit->Health() < 1.0) {
							$this->recreateHealth($unit);
						}
						if ($unit->Aura()) {
							$this->recreateAura($unit);
						}
					}
					break;
				case Type::MONSTER :
					$this->recreateMonster($unit);
					break;
				default :
					Lemuria::Log()->debug('Unit ' . $unit . ' of party type ' . $type->name . ' has no recreation yet.');
			}
		}
	}

	private function hasHunger(Unit $unit): bool {
		$effect = new Hunger($this->state);
		/** @var Hunger $hunger */
		return (bool)Lemuria::Score()->find($effect->setUnit($unit));
	}

	private function recreateHealth(Unit $unit): void {
		$boost      = $unit->Construction()?->Building() instanceof Tavern ? 2 : 1;
		$hitpoints  = $this->context->getCalculus($unit)->hitpoints();
		$health     = (int)floor($unit->Health() * $hitpoints);
		$difference = $hitpoints - $health;
		$heal       = min($difference, $boost * $unit->Race()->Hunger());
		$healed     = ($health + $heal) / $hitpoints;
		$unit->setHealth($healed);
		Lemuria::Log()->debug('Unit ' . $unit . ' regenerates ' . $heal . ' hitpoints.');
		$this->message(RecreateHealthMessage::class, $unit)->p($heal);
	}

	private function recreateAura(Unit $unit): void {
		$aura       = $unit->Aura();
		$current    = $aura->Aura();
		$maximum    = $aura->Maximum();
		$difference = $maximum - $current;
		$boost      = $this->isInActiveMagespire($unit) ? 2.0 : 1.0;
		if ($difference > 0) {
			$regain = min($difference, max(1, (int)floor($unit->Race()->Refill() * $boost * $maximum)));
			$aura->setAura($current + $regain);
			Lemuria::Log()->debug('Unit ' . $unit . ' regenerates ' . $regain . ' aura.');
			$this->message(RecreateAuraMessage::class, $unit)->p($regain);
		}
	}

	private function recreateMonster(Unit $unit): void {
		$health = $unit->Health();
		if ($health < 1.0) {
			/** @var Monster $monster */
			$monster    = $unit->Race();
			$recreation = $monster->Recreation();
			if ($recreation > 0.0) {
				$hitpoints  = $monster->Hitpoints();
				$health     = (int)floor($health * $hitpoints);
				$difference = $hitpoints - $health;
				$heal       = min($difference, (int)floor($recreation * $hitpoints));
				$healed     = ($health + $heal) / $hitpoints;
				$unit->setHealth($healed);
				Lemuria::Log()->debug('Unit ' . $unit . ' regenerates ' . $heal . ' hitpoints.');
				$this->message(RecreateHealthMessage::class, $unit)->p($heal);
			}
		}
	}
}
