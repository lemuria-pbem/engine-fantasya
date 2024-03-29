<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Effect\ExcessCargo;
use Lemuria\Engine\Fantasya\Message\Vessel\FounderEffectMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\FounderMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Navigable;
use Lemuria\Model\Fantasya\Vessel;

/**
 * This event determines if an overloaded ship that is lost at sea will take damage and finally sink.
 *
 * - If the ship has taken maximum damage at the beginning of this event, it will sink.
 * - An overloaded ship will result in an ExcessCargo effect.
 * - If this effect is active at the beginning of this event, the ship takes damage.
 * - Everyone aboard the sunken ship will die and all payload will be lost.
 */
final class Founder extends AbstractEvent
{
	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	protected function run(): void {
		foreach (Vessel::all() as $vessel) {
			$excessCargo = Lemuria::Score()->find($this->effect($vessel));
			if ($vessel->Region()->Landscape() instanceof Navigable) {
				$completion = $vessel->Completion();
				if ($completion <= 0.0) {
					$this->founder($vessel);
					if ($excessCargo) {
						Lemuria::Score()->remove($excessCargo);
					}
					$this->message(FounderMessage::class, $vessel)->e($vessel->Region());
				} else {
					if ($vessel->Passengers()->Weight() > $vessel->Ship()->Payload()) {
						if (!$excessCargo) {
							Lemuria::Score()->add($this->effect($vessel));
							$this->message(FounderEffectMessage::class, $vessel);
						}
					} elseif ($excessCargo) {
						Lemuria::Score()->remove($excessCargo);
					}
				}
			} else {
				if ($excessCargo) {
					Lemuria::Score()->remove($excessCargo);
				}
			}
		}
	}

	private function founder(Vessel $vessel): void {
		$passengers = $vessel->Passengers();
		foreach (clone $passengers as $unit) {
			$unit->Inventory()->clear();
			$unit->setSize(0);
			$passengers->remove($unit);
		}
		$vessel->Region()->Fleet()->remove($vessel);
	}

	private function effect(Vessel $vessel): ExcessCargo {
		$effect = new ExcessCargo($this->state);
		return $effect->setVessel($vessel);
	}
}
