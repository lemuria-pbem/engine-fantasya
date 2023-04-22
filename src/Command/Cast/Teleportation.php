<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Factory\GiftTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Factory\SplitTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\TeleportationErrorMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\TeleportationForeignMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\TeleportationGiftMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\TeleportationMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LoseToUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TeleportationMoveMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Engine\Fantasya\Travel\MoveTrait;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Distribution;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Unit;

final class Teleportation extends AbstractCast
{
	use BuilderTrait;
	use GiftTrait;
	use MessageTrait;
	use MoveTrait;
	use SplitTrait;

	public function cast(): void {
		$unit   = $this->cast->Unit();
		$region = $unit->Region();

		$target = $this->cast->Target();
		$there  = $target->Region();
		$level  = $this->cast->Level();

		if ($level > 0) {
			if ($target->Party() === $unit->Party()) {
				$calculus   = new Calculus($target);
				$payload    = $calculus->payload(1);
				$treasury   = $this->getWeightOfTreasury($target);
				$maxPayload = $level * $payload;
				if ($treasury > $maxPayload) {
					$this->message(TeleportationErrorMessage::class, $unit)->e($target);
					return;
				}

				$remaining = null;
				$size      = $target->Size();
				if ($size > $level) {
					$calculus      = new Calculus($target);
					$distributions = $calculus->inventoryDistribution();
					$remaining     = $this->splitUnit($target, $level);
					$this->distributeInventory($target, $remaining, $distributions);
				}
				$weight    = $target->Race()->Weight() + $payload;
				$maxWeight = $level * $weight;
				if ($target->Weight() > $maxWeight) {
					$excess = $this->removeExcessPayload($target, $maxPayload - $treasury);
					$this->giveExcessPayload($target, $excess, $remaining, $there);
				}
				$needed = (int)ceil($target->Weight() / $weight);
				if ($needed > $level) {
					throw new LemuriaException('Not enough Aura for teleporting, unit is too heavy.');
				}
				$aura = $needed * $this->cast->Spell()->Aura();

				$this->clearUnitStatus($target);
				$this->clearConstructionOwner($target);
				$this->clearVesselCaptain($target);
				$unit->Aura()->consume($aura);
				$there->Residents()->remove($target);
				$region->Residents()->add($target);
				$this->message(TeleportationMessage::class, $unit)->e($target);
				$this->message(TeleportationMoveMessage::class, $target)->e($unit);
			} else {
				$this->message(TeleportationForeignMessage::class, $unit)->e($target);
			}
		}
	}

	private function getWeightOfTreasury(Unit $unit): int {
		$weight = 0;
		foreach ($unit->Treasury() as $unicum) {
			$weight += $unicum->Composition()->Weight();
		}
		return $weight;
	}

	/**
	 * @param array<Distribution> $distributions
	 */
	private function distributeInventory(Unit $target, Unit $remaining, array $distributions): void {
		$from          = $target->Size();
		$fromInventory = $target->Inventory();
		$restInventory = $remaining->Inventory();
		foreach ($distributions as $distribution) {
			$size = $distribution->Size();
			if ($size > $from) {
				$move = $size - $from;
				foreach ($distribution as $quantity) {
					$moved = new Quantity($quantity->Commodity(), $move * $quantity->Count());
					$fromInventory->remove($moved);
					$restInventory->add(new Quantity($quantity->Commodity(), $moved->Count()));
				}
			}
		}
	}

	private function giveExcessPayload(Unit $unit, Resources $resources, ?Unit $remaining, Region $region): void {
		if ($remaining) {
			$remaining->Inventory()->fill($resources);
			$this->message(TeleportationGiftMessage::class, $remaining)->e($unit);
		} else {
			$intelligence = State::getInstance()->getIntelligence($region);
			$heirs        = $intelligence->getHeirs($unit);
			if (!$heirs->count()) {
				$heirs = $intelligence->getHeirs($unit, false);
			}
			foreach ($resources as $quantity) {
				$heir = $this->giftToRandom($heirs, $quantity);
				if ($heir) {
					$this->message(LoseToUnitMessage::class, $heir)->i($quantity)->e($unit);
				}
			}
		}
	}
}
