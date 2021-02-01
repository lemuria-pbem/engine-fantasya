<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Destroy;

use Lemuria\Engine\Lemuria\Activity;
use Lemuria\Engine\Lemuria\Command\UnitCommand;
use Lemuria\Engine\Lemuria\Exception\UnknownCommandException;
use Lemuria\Engine\Lemuria\Message\Unit\SmashDamageConstructionMessage;
use Lemuria\Engine\Lemuria\Message\Unit\SmashDamageVesselMessage;
use Lemuria\Engine\Lemuria\Message\Unit\SmashDestroyConstructionMessage;
use Lemuria\Engine\Lemuria\Message\Unit\SmashDestroyVesselMessage;
use Lemuria\Engine\Lemuria\Message\Unit\SmashLeaveConstructionMessage;
use Lemuria\Engine\Lemuria\Message\Unit\SmashLeaveVesselMessage;
use Lemuria\Engine\Lemuria\Message\Unit\SmashNotConstructionMessageOwnerMessage;
use Lemuria\Engine\Lemuria\Message\Unit\SmashNotInConstructionMessage;
use Lemuria\Engine\Lemuria\Message\Unit\SmashNotInVesselMessage;
use Lemuria\Engine\Lemuria\Message\Unit\SmashNotVesselOwnerMessage;
use Lemuria\Engine\Lemuria\Message\Unit\SmashRegainMessage;
use Lemuria\Id;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Requirement;
use Lemuria\Model\Lemuria\Resources;
use Lemuria\Model\Lemuria\Unit;

/**
 * Implementation of command ZERSTÖREN for constructions and vessels.
 *
 * This command is an ongoing activity that destroys parts of a building/ship until it is completely wiped out. The
 * destroying unit gets back some of the resources that were used when building the building/ship.
 *
 * - ZERSTÖREN Burg|Gebäude|Gebaeude <construction>
 * - ZERSTÖREN Schiff <vessel>
 */
final class Smash extends UnitCommand implements Activity
{
	private Id $id;

	protected function run(): void {
		$this->id = Id::fromId($this->phrase->getParameter(2));
		switch (strtolower($this->phrase->getParameter())) {
			case 'burg' :
			case 'gebäude' :
			case 'gebaeude' :
				$this->destroyConstruction();
				break;
			case 'schiff' :
				$this->destroyVessel();
				break;
			default :
				throw new UnknownCommandException($this);
		}
	}

	private function destroyConstruction(): void {
		$construction = $this->unit->Construction();
		if (!$construction || $construction->Id()->Id() !== $this->id->Id()) {
			$this->message(SmashNotInConstructionMessage::class);
			return;
		}
		$inhabitants = $construction->Inhabitants();
		if ($inhabitants->Owner() !== $this->unit) {
			$this->message(SmashNotConstructionMessageOwnerMessage::class)->e($construction);
			return;
		}
		foreach ($inhabitants as $unit /* @var Unit $unit */) {
			if ($unit !== $this->unit) {
				$inhabitants->remove($unit);
				$this->message(SmashLeaveConstructionMessage::class, $unit)->e($construction);
			}
		}

		$building = $construction->Building();
		$craft    = $building->getCraft();
		$material = $building->getMaterial();
		$size     = $construction->Size();
		$damage   = $this->destroy($craft, $material, $size);
		$remains  = $size - $damage;
		$construction->setSize($remains);
		if ($remains > 0) {
			$this->message(SmashDamageConstructionMessage::class)->e($construction)->p($damage);
		} else {
			$this->message(SmashDestroyConstructionMessage::class)->e($construction);
		}
	}

	private function destroyVessel(): void {
		$vessel = $this->unit->Vessel();
		if (!$vessel || $vessel->Id()->Id() !== $this->id->Id()) {
			$this->message(SmashNotInVesselMessage::class);
			return;
		}
		$passengers = $vessel->Passengers();
		if ($passengers->Owner() !== $this->unit) {
			$this->message(SmashNotVesselOwnerMessage::class)->e($vessel);
			return;
		}
		foreach ($passengers as $unit /* @var Unit $unit */) {
			if ($unit !== $this->unit) {
				$passengers->remove($unit);
				$this->message(SmashLeaveVesselMessage::class, $unit)->e($vessel);
			}
		}

		$ship     = $vessel->Ship();
		$craft    = $ship->getCraft();
		$material = $ship->getMaterial();
		$wood     = $ship->Wood();
		$size     = (int)round($vessel->Completion() * $wood);
		$damage   = $this->destroy($craft, $material, $size);
		$remains  = $size - $damage;
		$vessel->setCompletion($remains / $wood);
		if ($remains > 0) {
			$this->message(SmashDamageVesselMessage::class)->e($vessel)->p($damage);
		} else {
			$this->message(SmashDestroyVesselMessage::class)->e($vessel);
		}
	}

	private function destroy(Requirement $craft, Resources $material, int $size): int {
		$level      = $this->calculus()->knowledge($craft->Talent())->Level();
		$capability = $level > 1 ? $this->unit->Size() * $level : $this->unit->Size();
		$damage     = $capability < $size ? $capability : $size;
		foreach ($material as $quantity /* @var Quantity $quantity */) {
			$regain = new Quantity($quantity->Commodity(), $damage * $quantity->Count());
			$this->unit->Inventory()->add($regain);
			$this->message(SmashRegainMessage::class)->i($regain);
		}
		return $damage;
	}
}
