<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Create;

use Lemuria\Engine\Lemuria\Message\Unit\VesselBuildMessage;
use Lemuria\Engine\Lemuria\Message\Unit\VesselCreateMessage;
use Lemuria\Engine\Lemuria\Message\Unit\VesselExperienceMessage;
use Lemuria\Engine\Lemuria\Message\Unit\VesselMessage;
use Lemuria\Engine\Lemuria\Message\Unit\VesselOnlyMessage;
use Lemuria\Engine\Lemuria\Message\Unit\VesselResourcesMessage;
use Lemuria\Engine\Lemuria\Message\Unit\VesselUnableMessage;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Requirement;
use Lemuria\Model\Lemuria\Ship;
use Lemuria\Model\Lemuria\Vessel as VesselModel;

/**
 * Implementation of command MACHEN <Ship> (create ship).
 *
 * The command lets units build vessels. If the unit is inside a vessel, that vessel is built further.
 *
 * - MACHEN Schiff
 * - MACHEN Schiff <size>
 * - MACHEN <Ship>
 * - MACHEN <Ship> <size>
 */
final class Vessel extends AbstractProduct
{
	private int $remaining;

	protected function run(): void {
		$vessel           = $this->unit->Vessel();
		$ship             = $vessel?->Ship() ?: $this->getShip();
		$size             = $vessel?->getUsedWood() ?? 0;
		$wood             = $ship->Wood();
		$this->remaining  = $vessel?->getRemainingWood() ?? $wood;
		$demand           = $this->job->Count();
		$talent           = $ship->getCraft()->Talent();
		$this->capability = $this->calculateProduction($ship->getCraft());
		$reserve          = $this->calculateResources($ship->getMaterial());
		$production       = min($this->capability, $reserve);
		if ($production > 0) {
			$yield = min($production, $demand);
			foreach ($ship->getMaterial() as $quantity /* @var Quantity $quantity */) {
				$consumption = new Quantity($quantity->Commodity(), $yield * $quantity->Count());
				$this->unit->Inventory()->remove($consumption);
			}

			if ($vessel) {
				$vessel->setCompletion(($size + $yield) / $wood);
				if ($this->job->hasCount() && $demand > $production) {
					$this->message(VesselOnlyMessage::class)->e($vessel)->p($yield);
				} else {
					$this->message(VesselBuildMessage::class)->e($vessel)->p($yield);
				}
			} else {
				$id     = Lemuria::Catalog()->nextId(Catalog::VESSELS);
				$vessel = new VesselModel();
				$vessel->setName('Schiff ' . $id)->setId($id);
				$vessel->Passengers()->add($this->unit);
				$this->unit->Region()->Fleet()->add($vessel);
				$vessel->setCompletion($yield / $wood);
				if ($this->job->hasCount() && $demand > $production) {
					$this->message(VesselOnlyMessage::class)->e($vessel)->p($yield);
				} else {
					$this->message(VesselMessage::class)->s($ship);
				}
			}
		} else {
			if ($this->capability > 0) {
				if ($vessel) {
					$this->message(VesselResourcesMessage::class)->e($vessel);
				} else {
					$this->message(VesselCreateMessage::class)->s($ship);
				}
			} else {
				if ($vessel) {
					$this->message(VesselExperienceMessage::class)->e($vessel)->s($talent);
				} else {
					$this->message(VesselUnableMessage::class)->s($ship);
				}
			}
		}
	}

	/**
	 * Get maximum amount that can be produced by knowledge.
	 */
	protected function calculateProduction(Requirement $craft): int {
		$production = parent::calculateProduction($craft);
		return $production <= $this->remaining ? $production : $this->remaining;
	}

	private function getShip(): Ship {
		$resource = $this->job->getObject();
		if ($resource instanceof Ship) {
			return $resource;
		}
		throw new LemuriaException('Expected a ship resource.');
	}
}
