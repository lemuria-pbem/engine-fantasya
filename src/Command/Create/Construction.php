<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use Lemuria\Engine\Fantasya\Factory\MarketBuilder;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionBuildMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionCreateMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionDependencyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionResourcesMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionUnableMessage;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Fantasya\Building;
use Lemuria\Model\Fantasya\Building\AbstractCastle;
use Lemuria\Model\Fantasya\Building\Castle;
use Lemuria\Model\Fantasya\Building\Site;
use Lemuria\Model\Fantasya\Construction as ConstructionModel;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Requirement;

/**
 * Implementation of command MACHEN <Gebäude> (create construction).
 *
 * The command lets units build constructions. If the unit is inside a construction, that construction is built further.
 *
 * - MACHEN Burg|Gebäude|Gebaeude
 * - MACHEN Burg|Gebäude|Gebaeude <size>
 * - MACHEN <Building>
 * - MACHEN <Building> <size>
 */
final class Construction extends AbstractProduct
{
	private int $size;

	private bool $hasMarket = false;

	protected function initialize(): void {
		parent::initialize();
		$castle = $this->context->getIntelligence($this->unit->Region())->getGovernment();
		if ($castle?->Size() > Site::MAX_SIZE) {
			$this->hasMarket = true;
		}
	}

	protected function run(): void {
		$construction = $this->unit->Construction();
		$building     = $construction?->Building() ?? $this->getBuilding();
		if (!$this->checkDependency($building)) {
			$dependency = $building->Dependency();
			$this->message(ConstructionDependencyMessage::class)->s($building)->s($dependency, ConstructionDependencyMessage::DEPENDENCY);
			return;
		}

		$this->size       = $construction?->Size() ?? 0;
		$demand           = $this->job->Count();
		$talent           = $building->getCraft()->Talent();
		$this->capability = $this->calculateProduction($building->getCraft());
		$reserve          = $this->calculateResources($building->getMaterial());
		$production       = min($this->capability, $reserve);
		if ($production > 0) {
			$yield = min($production, $demand);
			foreach ($building->getMaterial() as $quantity /* @var Quantity $quantity */) {
				$consumption = new Quantity($quantity->Commodity(), $yield * $quantity->Count());
				$this->unit->Inventory()->remove($consumption);
			}

			if ($construction) {
				$construction->setSize($construction->Size() + $yield);
				if ($this->job->hasCount() && $demand > $production) {
					$this->message(ConstructionOnlyMessage::class)->e($construction)->p($yield);
				} else {
					$this->message(ConstructionBuildMessage::class)->e($construction)->p($yield);
				}
			} else {
				$id           = Lemuria::Catalog()->nextId(Catalog::CONSTRUCTIONS);
				$construction = new ConstructionModel();
				$construction->setName('Gebäude ' . $id)->setId($id);
				$construction->Inhabitants()->add($this->unit);
				$this->unit->Region()->Estate()->add($construction);
				$construction->setBuilding($building)->setSize($yield);
				if ($this->job->hasCount() && $demand > $production) {
					$this->message(ConstructionOnlyMessage::class)->e($construction)->p($yield);
				} else {
					$this->message(ConstructionMessage::class)->s($construction->Building());
				}
			}
			$this->addToWorkload($yield);
			$this->initializeMarket($construction);
		} else {
			if ($this->capability > 0) {
				if ($construction) {
					$this->message(ConstructionResourcesMessage::class)->e($construction);
				} else {
					$this->message(ConstructionCreateMessage::class)->s($building);
				}
			} else {
				if ($construction) {
					$this->message(ConstructionExperienceMessage::class)->e($construction)->s($talent);
				} else {
					$this->message(ConstructionUnableMessage::class)->s($building);
				}
			}
		}
	}

	protected function checkDependency(Building $building): bool {
		$dependency = $building->Dependency();
		if ($dependency) {
			foreach ($this->unit->Region()->Estate() as $construction /* @var ConstructionModel $construction */) {
				if ($construction->Building() === $dependency) {
					if ($construction->Inhabitants()->Owner()?->Party() === $this->unit->Party()) {
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Get maximum amount that can be produced by knowledge.
	 */
	protected function calculateProduction(Requirement $craft): int {
		if ($this->job->getObject() instanceof Castle) {
			$production = $this->calculateCastleProduction($this->size);
			$this->reduceByWorkload($production);
			return $production;
		}
		return parent::calculateProduction($craft);
	}

	private function calculateCastleProduction(int $size, int $pointsUsed = 0): int {
		$castle = AbstractCastle::forSize($size);
		$craft  = $castle->getCraft();
		$talent = $craft->Talent();
		$cost   = $craft->Level();
		$level  = $this->calculus()->knowledge($talent::class)->Level();
		if ($level < $cost) {
			return 0;
		}

		$unitSize   = $this->unit->Size();
		$points     = (int)floor($this->potionBoost($unitSize) * $unitSize * $level) - $pointsUsed;
		$production = (int)floor($points / $cost);
		$newSize    = $size + $production;
		$maxSize    = $castle->MaxSize();
		if ($newSize <= $maxSize) {
			return $production;
		}

		$newSize     = $maxSize;
		$production  = $newSize - $size;
		$delta       = $production * $cost;
		$points     -= $delta;
		$pointsUsed += $delta;
		$castle      = $castle->Upgrade();
		$cost        = $castle->getCraft()->Level();
		if ($level < $cost || $points < $cost) {
			return $production;
		}

		$production++;
		$newSize++;
		$pointsUsed += $cost;
		return $production + $this->calculateCastleProduction($newSize, $pointsUsed);
	}

	private function getBuilding(): Building {
		$resource = $this->job->getObject();
		if ($resource instanceof Building) {
			return $resource;
		}
		throw new LemuriaException('Expected a building resource.');
	}

	private function initializeMarket(ConstructionModel $construction): void {
		if ($this->hasMarket) {
			return;
		}
		if ($construction->Building() instanceof Castle && $construction->Size() > Site::MAX_SIZE) {
			$region        = $construction->Region();
			$marketBuilder = new MarketBuilder($this->context->getIntelligence($region));
			$marketBuilder->initPrices();
			Lemuria::Log()->debug('Market opens the first time in region ' . $region . ' - prices have been initialized.');
		}
	}
}
