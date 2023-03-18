<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use Lemuria\Engine\Fantasya\Factory\Model\AnyShip;
use Lemuria\Engine\Fantasya\Factory\Model\Dockyards;
use Lemuria\Engine\Fantasya\Factory\Model\Job;
use Lemuria\Engine\Fantasya\Factory\ModifiedActivityTrait;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveVesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VesselAlreadyFinishedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VesselBuildMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VesselCreateMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VesselExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VesselOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VesselResourcesMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VesselSpaceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VesselUnableMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\VesselFinishedMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Requirement;
use Lemuria\Model\Fantasya\Ship;
use Lemuria\Model\Fantasya\Vessel as VesselModel;

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
	use ModifiedActivityTrait;

	private int $remaining;

	private ?Construction $port = null;

	protected function initialize(): void {
		$this->replacePlaceholderJob();
		parent::initialize();
	}

	protected function run(): void {
		$ship   = $this->getShip();
		$vessel = $this->leaveCurrentVesselFor($ship);
		if (!$vessel && !$this->canBuildVesselHere($ship)) {
			$this->message(VesselSpaceMessage::class)->s($ship);
			return;
		}

		if ($vessel?->Completion() < 1.0) {
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
				foreach ($ship->getMaterial() as $quantity) {
					$count       = (int)ceil($this->consumption * $yield * $quantity->Count());
					$consumption = new Quantity($quantity->Commodity(), $count);
					$this->unit->Inventory()->remove($consumption);
				}

				if ($vessel) {
					$vessel->setCompletion(($size + $yield) / $wood);
					if ($this->job->hasCount() && $demand > $production && $demand < PHP_INT_MAX) {
						$this->message(VesselOnlyMessage::class)->e($vessel)->p($yield);
					} else {
						$this->message(VesselBuildMessage::class)->e($vessel)->p($yield);
					}
				} else {
					$id     = Lemuria::Catalog()->nextId(Domain::Vessel);
					$vessel = new VesselModel();
					$vessel->setName('Schiff ' . $id)->setId($id);
					$vessel->Passengers()->add($this->unit);
					$this->unit->Region()->Fleet()->add($vessel);
					$vessel->setShip($ship)->setPort($this->port)->setCompletion($yield / $wood);
					if ($this->job->hasCount() && $demand > $production && $demand < PHP_INT_MAX) {
						$this->message(VesselOnlyMessage::class)->e($vessel)->p($yield);
					} else {
						$this->message(VesselMessage::class)->s($ship);
					}
				}
				if ($vessel->Completion() === 1.0) {
					$this->message(VesselFinishedMessage::class, $vessel);
					$this->preventDefault();
				} else {
					$this->newDefault = new Vessel(new Phrase('MACHEN Schiff'), $this->context, $this->job);
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
						$this->message(VesselUnableMessage::class)->s($ship)->s($talent, VesselUnableMessage::TALENT);
					}
				}
			}
		} else {
			$this->preventDefault();
			$this->message(VesselAlreadyFinishedMessage::class)->e($vessel);
		}
	}

	/**
	 * Get maximum amount that can be produced by knowledge.
	 */
	protected function calculateProduction(Requirement $craft): int {
		$production = parent::calculateProduction($craft);
		return min($production, $this->remaining);
	}

	private function replacePlaceholderJob(): void {
		$ship = $this->job->getObject();
		if ($ship instanceof AnyShip) {
			$ship = $this->unit->Vessel()?->Ship();
			if ($ship) {
				$this->job = new Job($ship, $this->job->Count());
			}
		}
	}

	private function leaveCurrentVesselFor(Ship $ship): ?VesselModel {
		$vessel = $this->unit->Vessel();
		if ($vessel && $vessel->Ship() !== $ship) {
			$vessel->Passengers()->remove($this->unit);
			$this->message(LeaveVesselMessage::class)->e($vessel);
		}
		return $vessel;
	}

	private function getShip(): Ship {
		$resource = $this->job->getObject();
		if ($resource instanceof Ship) {
			return $resource;
		}
		throw new LemuriaException('Expected a ship resource.');
	}

	private function canBuildVesselHere(Ship $ship): bool {
		$dockyards = new Dockyards($ship, $this->unit);
		if ($dockyards->CanBuildHere()) {
			return true;
		}
		$this->port = $dockyards->Port();
		return (bool)$this->port;
	}
}
