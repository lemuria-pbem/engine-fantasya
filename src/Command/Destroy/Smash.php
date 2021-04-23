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
use Lemuria\Engine\Fantasya\Message\Unit\SmashDestroyRoadMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashDestroyVesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashLeaveConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashLeaveVesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashNoRoadMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashNoRoadToMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashNotConstructionMessageOwnerMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashNotInConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashNotInVesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashNotVesselOwnerMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashRegainMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SmashRoadGuardedMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Stone;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Requirement;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Talent\Roadmaking;
use Lemuria\Model\Fantasya\Unit;

/**
 * Implementation of command ZERSTÖREN for constructions and vessels.
 *
 * This command is an ongoing activity that destroys parts of a building/ship until it is completely wiped out. The
 * destroying unit gets back some of the resources that were used when building the building/ship.
 *
 * - ZERSTÖREN Burg|Gebäude|Gebaeude <construction>
 * - ZERSTÖREN Schiff <vessel>
 * - ZERSTÖREN Straße|Strasse <direction>
 */
final class Smash extends UnitCommand implements Activity
{
	use ModifiedActivityTrait;
	use WorkloadTrait;

	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->initWorkload();
	}

	protected function run(): void {
		if ($this->phrase->count() !== 2) {
			throw new UnknownCommandException($this);
		}

		$param = $this->phrase->getParameter(2);
		switch (strtolower($this->phrase->getParameter())) {
			case 'burg' :
			case 'gebäude' :
			case 'gebaeude' :
				$this->destroyConstruction(Id::fromId($param));
				break;
			case 'schiff' :
				$this->destroyVessel(Id::fromId($param));
				break;
			case 'straße' :
			case 'strasse' :
				$this->destroyRoad($this->phrase->getParameter(2));
				break;
			default :
				throw new UnknownCommandException($this);
		}
	}

	private function destroyConstruction(Id $id): void {
		$construction = $this->unit->Construction();
		if (!$construction || $construction->Id()->Id() !== $id->Id()) {
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
			Lemuria::Catalog()->reassign($construction);
			$construction->Inhabitants()->remove($this->unit);
			$construction->Region()->Estate()->remove($construction);
			Lemuria::Catalog()->remove($construction);
			$this->message(SmashDestroyConstructionMessage::class)->e($construction);
		}
	}

	private function destroyVessel(Id $id): void {
		$vessel = $this->unit->Vessel();
		if (!$vessel || $vessel->Id()->Id() !== $id->Id()) {
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
			Lemuria::Catalog()->reassign($vessel);
			$vessel->Passengers()->remove($this->unit);
			$vessel->Region()->Fleet()->remove($vessel);
			Lemuria::Catalog()->remove($vessel);
			$this->message(SmashDestroyVesselMessage::class)->e($vessel);
		}
	}

	private function destroyRoad(string $direction): void {
		$direction  = $this->context->Factory()->direction($direction);
		$region     = $this->unit->Region();
		$landscape  = $region->Landscape();
		$roadStones = $landscape->RoadStones();
		if ($roadStones <= 0) {
			$this->message(SmashNoRoadMessage::class)->e($region);
			return;
		}
		$roads = $region->Roads();
		if (!isset($roads[$direction])) {
			$this->message(SmashNoRoadToMessage::class)->e($region)->p($direction);
			return;
		}
		if ($this->getCheckByAgreement(Relation::GUARD)) {
			$this->message(SmashRoadGuardedMessage::class)->e($region);
			return;
		}

		$craft      = new Requirement(self::createTalent(Roadmaking::class));
		$stone      = self::createCommodity(Stone::class);
		$completion = $roads[$direction];
		$size       = (int)round($completion * $roadStones);
		$material   = new Resources();
		$material->add(new Quantity($stone, 1));
		$damage = $this->destroy($craft, $material, $size);
		$this->addToWorkload($damage);
		$remains = $size - $damage;
		if ($remains > 0) {
			$roads[$direction] = $remains / $roadStones;
			$this->newDefault  = $this;
			$regain            = new Quantity($stone, $damage);
			$this->message(SmashDestroyRoadMessage::class)->e($region)->p($direction)->i($regain);
		} else {
			unset($roads[$direction]);
			$this->message(SmashDestroyRoadMessage::class)->e($region)->p($direction);
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
