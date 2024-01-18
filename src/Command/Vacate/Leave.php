<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Vacate;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Effect\UnpaidDemurrage;
use Lemuria\Engine\Fantasya\Factory\GiftTrait;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Message\Construction\LeaveNewOwnerMessage;
use Lemuria\Engine\Fantasya\Message\Construction\LeaveNoOwnerMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveNotMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveSiegeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveVesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveVesselUnpaidDemurrageMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\LeaveNewCaptainMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\LeaveNoCaptainMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Luxury;
use Lemuria\Model\Fantasya\Navigable;
use Lemuria\Model\Fantasya\Protection;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\RawMaterial;

/**
 * A unit leaves the construction or vessel using the Leave command.
 *
 * - VERLASSEN
 */
final class Leave extends UnitCommand
{
	use GiftTrait;
	use SiegeTrait;

	protected function run(): void {
		$construction = $this->unit->Construction();
		if ($construction) {
			if ($this->initSiege($construction)->canEnterOrLeave($this->unit)) {
				$inhabitants = $construction->Inhabitants();
				$owner       = $inhabitants->Owner();
				$inhabitants->remove($this->unit);
				$this->message(LeaveConstructionMessage::class)->e($construction);
				$newOwner = $inhabitants->Owner();
				if ($newOwner) {
					if ($newOwner !== $owner) {
						$this->message(LeaveNewOwnerMessage::class, $construction)->e($newOwner);
					}
				} else {
					$this->message(LeaveNoOwnerMessage::class, $construction);
				}
			} else {
				$this->message(LeaveSiegeMessage::class);
			}
		} else {
			$vessel = $this->unit->Vessel();
			if ($vessel) {
				$effect = new UnpaidDemurrage(State::getInstance());
				if (Lemuria::Score()->find($effect->setVessel($vessel))) {
					$this->message(LeaveVesselUnpaidDemurrageMessage::class)->e($vessel);
				} else {
					$passengers = $vessel->Passengers();
					$captain    = $passengers->Owner();
					$passengers->remove($this->unit);
					$this->message(LeaveVesselMessage::class)->e($vessel);
					$newCaptain = $passengers->Owner();
					if ($newCaptain) {
						if ($newCaptain !== $captain) {
							$this->message(LeaveNewCaptainMessage::class, $vessel)->e($newCaptain);
						}
					} else {
						$this->message(LeaveNoCaptainMessage::class, $vessel);
					}
					if ($this->unit->Region()->Landscape() instanceof Navigable) {
						$this->loseExcessInventoryAtSea();
					}
				}
			} else {
				$this->message(LeaveNotMessage::class);
			}
		}
	}

	protected function checkSize(): bool {
		return true;
	}

	private function loseExcessInventoryAtSea(): void {
		$sorted    = [];
		$silver    = 0.0;
		$inventory = $this->unit->Inventory();
		$person    = $this->calculus()->payload(1);
		foreach ($inventory as $quantity) {
			$commodity = $quantity->Commodity();
			$weight    = $commodity->Weight();
			if ($weight > $person || !$this->canKeep($commodity)) {
				$inventory->remove($quantity);
				$this->giftToRandomUnit($quantity);
			} else {
				$sorted[$weight][] = $quantity;
				if ($commodity instanceof Silver) {
					$silver = $quantity->Count() / $this->unit->Size();
				}
			}
		}
		ksort($sorted);

		$payload = $this->calculus()->payload();
		foreach ($sorted as $quantity) {
			/** @var Quantity $quantity */
			$commodity = $quantity->Commodity();
			if ($commodity instanceof Silver) {
				if ($silver > 100.0) {
					$count = 100 * $this->unit->Size();
					$lose  = $quantity->Count() - $count;
					$inventory->remove(new Quantity($commodity, $lose));
					$this->giftToRandomUnit(new Quantity($commodity, $lose));
					$quantity = new Quantity($commodity, $count);
				}
			}
			if ($quantity->Weight() > $payload) {
				$count = $quantity->Count() - (int)floor($payload / $commodity->Weight());
				$inventory->remove(new Quantity($commodity, $count));
				$this->giftToRandomUnit(new Quantity($commodity, $count));
			}
			$payload -= $quantity->Weight();
		}
	}

	private function canKeep(Commodity $commodity): bool {
		if ($commodity instanceof Luxury) {
			return false;
		}
		if ($commodity instanceof Protection) {
			return false;
		}
		if ($commodity instanceof RawMaterial) {
			return false;
		}
		return true;
	}
}
