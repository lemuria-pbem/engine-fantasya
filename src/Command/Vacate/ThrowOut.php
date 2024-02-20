<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Vacate;

use Lemuria\Engine\Fantasya\Combat\Army;
use Lemuria\Engine\Fantasya\Combat\BattlePlace;
use Lemuria\Engine\Fantasya\Combat\Combat;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Effect\UnpaidDemurrage;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\GiftTrait;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Message\Construction\LeaveNewOwnerMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ThrowOutFromConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ThrowOutFromVesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ThrowOutNotHereMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ThrowOutNotMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ThrowOutOwnMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ThrowOutSiegeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ThrowOutSuperiorityMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ThrowOutSupportMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ThrowOutUnitConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ThrowOutUnitVesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ThrowOutVesselUnpaidDemurrageMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\LeaveNewCaptainMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Gathering;
use Lemuria\Model\Fantasya\Inhabitants;
use Lemuria\Model\Fantasya\Luxury;
use Lemuria\Model\Fantasya\Navigable;
use Lemuria\Model\Fantasya\Protection;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\RawMaterial;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Unit;

/**
 * A unit is forced to leave the construction or vessel.
 *
 * - VERLASSEN <Unit>
 */
final class ThrowOut extends UnitCommand
{
	use GiftTrait;
	use SiegeTrait;

	private const int SUPERIORITY = 6;

	protected function run(): void {
		if ($this->phrase->count() !== 1) {
			throw new InvalidCommandException($this);
		}

		$i    = 1;
		$id   = '';
		$unit = $this->nextId($i, $id);
		if ($unit->Party()->Id()->Id() === $this->unit->Party()->Id()->Id()) {
			$this->message(ThrowOutOwnMessage::class)->e($unit);
			return;
		}

		$construction = $this->unit->Construction();
		if ($construction) {
			if ($unit->Construction() !== $construction) {
				$this->message(ThrowOutNotHereMessage::class)->e($unit);
				return;
			}
			$support = new Gathering();
			if ($this->context->getTurnOptions()->IsSimulation() || !$this->hasSuperiorityOver($unit, $construction->Inhabitants(), $support)) {
				$this->message(ThrowOutSuperiorityMessage::class)->e($unit);
				foreach ($support as $party) {
					$this->message(ThrowOutSupportMessage::class)->e($unit)->e($party, ThrowOutSupportMessage::SUPPORT);
				}
				return;
			}
			if ($this->initSiege($construction)->canEnterOrLeave($unit)) {
				$inhabitants = $construction->Inhabitants();
				$owner       = $inhabitants->Owner();
				$inhabitants->remove($unit);
				$this->message(ThrowOutUnitConstructionMessage::class)->e($unit);
				$this->message(ThrowOutFromConstructionMessage::class, $unit)->e($this->unit)->e($construction, ThrowOutFromConstructionMessage::CONSTRUCTION);
				$newOwner = $inhabitants->Owner();
				if ($newOwner !== $owner) {
					$this->message(LeaveNewOwnerMessage::class)->e($newOwner);
				}
			} else {
				$this->message(ThrowOutSiegeMessage::class)->e($unit);
			}
		} else {
			$vessel = $this->unit->Vessel();
			if ($vessel) {
				if ($unit->Vessel() !== $vessel) {
					$this->message(ThrowOutNotHereMessage::class)->e($unit);
					return;
				}
				$support = new Gathering();
				if ($this->context->getTurnOptions()->IsSimulation() || !$this->hasSuperiorityOver($unit, $vessel->Passengers(), $support)) {
					$this->message(ThrowOutSuperiorityMessage::class)->e($unit);
					foreach ($support as $party) {
						$this->message(ThrowOutSupportMessage::class)->e($unit)->e($party, ThrowOutSupportMessage::SUPPORT);
					}
					return;
				}
				$effect = new UnpaidDemurrage(State::getInstance());
				if (Lemuria::Score()->find($effect->setVessel($vessel))) {
					$this->message(ThrowOutVesselUnpaidDemurrageMessage::class)->e($unit)->e($vessel, ThrowOutFromVesselMessage::VESSEL);
				} else {
					$passengers = $vessel->Passengers();
					$captain    = $passengers->Owner();
					$passengers->remove($unit);
					$this->message(ThrowOutUnitVesselMessage::class)->e($unit);
					$this->message(ThrowOutFromVesselMessage::class, $unit)->e($this->unit)->e($vessel, ThrowOutFromVesselMessage::VESSEL);
					$newCaptain = $passengers->Owner();
					if ($newCaptain !== $captain) {
						$this->message(LeaveNewCaptainMessage::class)->e($newCaptain);
					}
					if ($this->unit->Region()->Landscape() instanceof Navigable) {
						$this->loseExcessInventoryAtSea($unit);
					}
				}
			} else {
				$this->message(ThrowOutNotMessage::class);
			}
		}
	}

	protected function checkSize(): bool {
		return true;
	}

	private function hasSuperiorityOver(Unit $pariah, Inhabitants $people, Gathering $support): bool {
		$we       = $this->unit->Party()->Id()->Id();
		$they     = $pariah->Party()->Id()->Id();
		$strength = [$we => 0, $they => 0];
		foreach ($people as $unit) {
			$party = $unit->Party();
			$id    = $party->Id()->Id();
			if ($id === $we) {
				$strength[$we] += $this->getArmedUnitSize($unit);
			} elseif ($id === $they) {
				$strength[$they] += $unit->Size();
			} else {
				if ($party->Diplomacy()->has(Relation::COMBAT, $unit)) {
					$strength[$they] += $unit->Size();
					$support->add($party);
				}
			}
		}
		return $strength[$we] >= self::SUPERIORITY * $strength[$they];
	}

	private function getArmedUnitSize(Unit $unit): int {
		$size   = 0;
		$combat = new Combat($this->context, new BattlePlace($unit));
		$army   = new Army($unit->Party(), $combat);
		foreach ($this->context->getCalculus($unit)->gearDistribution() as $distribution) {
			$combatant = new Combatant($army, $unit);
			if (!$combatant->setBattleRow(BattleRow::Front)->setDistribution($distribution)->WeaponSkill()->isUnarmed()) {
				$size += $distribution->Size();
			} else {
				if (!$combatant->setBattleRow(BattleRow::Back)->WeaponSkill()->isUnarmed()) {
					$size += $distribution->Size();
				}
			}
		}
		return $size;
	}

	private function loseExcessInventoryAtSea(Unit $unit): void {
		$sorted    = [];
		$silver    = 0.0;
		$calculus  = $this->context->getCalculus($unit);
		$inventory = $unit->Inventory();
		$person    = $calculus->payload(1);
		foreach ($inventory as $quantity) {
			$commodity = $quantity->Commodity();
			$weight    = $commodity->Weight();
			if ($weight > $person || !$this->canKeep($commodity)) {
				$inventory->remove($quantity);
				$this->giftToRandomUnit($quantity, $unit);
			} else {
				$sorted[$weight][] = $quantity;
				if ($commodity instanceof Silver) {
					$silver = $quantity->Count() / $unit->Size();
				}
			}
		}
		ksort($sorted);

		$payload = $calculus->payload();
		foreach ($sorted as $quantity) {
			/** @var Quantity $quantity */
			$commodity = $quantity->Commodity();
			if ($commodity instanceof Silver) {
				if ($silver > 100.0) {
					$count = 100 * $unit->Size();
					$lose  = $quantity->Count() - $count;
					$inventory->remove(new Quantity($commodity, $lose));
					$this->giftToRandomUnit(new Quantity($commodity, $lose), $unit);
					$quantity = new Quantity($commodity, $count);
				}
			}
			if ($quantity->Weight() > $payload) {
				$count = $quantity->Count() - (int)floor($payload / $commodity->Weight());
				$inventory->remove(new Quantity($commodity, $count));
				$this->giftToRandomUnit(new Quantity($commodity, $count), $unit);
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
