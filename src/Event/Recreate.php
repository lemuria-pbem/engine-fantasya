<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Effect\Hunger;
use Lemuria\Engine\Fantasya\Message\Unit\RecreateAuraMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RecreateHealthMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Unit;

/**
 * Units regain health and aura.
 */
final class Recreate extends AbstractEvent
{
	public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Catalog::UNITS) as $unit /* @var Unit $unit */) {
			if ($unit->Party()->Type() !== Party::PLAYER) {
				continue;
			}

			if (!$this->hasHunger($unit)) {
				if ($unit->Health() < 1.0) {
					$this->recreateHealth($unit);
				}
				if ($unit->Aura()) {
					$this->recreateAura($unit);
				}
			}
		}
	}

	private function hasHunger(Unit $unit): bool {
		$effect = new Hunger($this->state);
		/** @var Hunger $hunger */
		return (bool)Lemuria::Score()->find($effect->setUnit($unit));
	}

	private function recreateHealth(Unit $unit): void {
		$hitpoints  = $this->context->getCalculus($unit)->hitpoints();
		$health     = (int)floor($unit->Health() * $hitpoints);
		$difference = $hitpoints - $health;
		$heal       = min($difference, $unit->Race()->Hunger());
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
		if ($difference > 0) {
			$regain = min($difference, max(1, (int)floor($unit->Race()->Refill() * $maximum)));
			$aura->setAura($current + $regain);
			Lemuria::Log()->debug('Unit ' . $unit . ' regenerates ' . $regain . ' aura.');
			$this->message(RecreateAuraMessage::class, $unit)->p($regain);
		}
	}
}
