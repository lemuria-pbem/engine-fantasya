<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Destroy;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\ModifiedActivityTrait;
use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\WorkloadTrait;
use Lemuria\Engine\Fantasya\Message\Unit\SmashDamageConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashDamageVesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashDestroyConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashDestroyVesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashLeaveConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashLeaveVesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashNotConstructionMessageOwnerMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashNotInConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashNotInVesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashNotVesselOwnerMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashRegainMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Requirement;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Unit;

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
	use ModifiedActivityTrait;
	use WorkloadTrait;

	private Id $id;

	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->initWorkload();
	}

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
		$this->addToWorkload($damage);
		$remains  = $size - $damage;
		$construction->setSize($remains);
		if ($remains > 0) {
			$this->newDefault = $this;
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
		$this->addToWorkload($damage);
		$remains  = $size - $damage;
		$vessel->setCompletion($remains / $wood);
		if ($remains > 0) {
			$this->newDefault = $this;
			$this->message(SmashDamageVesselMessage::class)->e($vessel)->p($damage);
		} else {
			$this->message(SmashDestroyVesselMessage::class)->e($vessel);
		}
	}

	private function destroy(Requirement $craft, Resources $material, int $size): int {
		$level      = $this->calculus()->knowledge($craft->Talent())->Level();
		$capability = $level > 1 ? $this->unit->Size() * $level : $this->unit->Size();
		$capability = $this->reduceByWorkload($capability);
		$damage     = $capability < $size ? $capability : $size;
		foreach ($material as $quantity /* @var Quantity $quantity */) {
			$regain = new Quantity($quantity->Commodity(), $damage * $quantity->Count());
			$this->unit->Inventory()->add($regain);
			$this->message(SmashRegainMessage::class)->i($regain);
		}
		return $damage;
	}
}
