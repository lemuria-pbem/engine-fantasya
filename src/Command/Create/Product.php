<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Create;

use Lemuria\Engine\Lemuria\Activity;
use Lemuria\Engine\Lemuria\Command\UnitCommand;
use Lemuria\Engine\Lemuria\Message\Unit\ProductCreateExperienceMessage;
use Lemuria\Engine\Lemuria\Message\Unit\ProductCreateMaterialMessage;
use Lemuria\Engine\Lemuria\Message\Unit\ProductCreateMessage;
use Lemuria\Engine\Lemuria\Message\Unit\ProductCreateOnlyMessage;
use Lemuria\Engine\Lemuria\Message\Unit\ProductOutputExperienceMessage;
use Lemuria\Engine\Lemuria\Message\Unit\ProductOutputMaterialMessage;
use Lemuria\Engine\Lemuria\Message\Unit\ProductMaterialOnlyMessage;
use Lemuria\Engine\Lemuria\Message\Unit\ProductOutputMessage;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Lemuria\Artifact;
use Lemuria\Model\Lemuria\Commodity;
use Lemuria\Model\Lemuria\Material;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Requirement;
use Lemuria\Model\Lemuria\Resources;

/**
 * Implementation of command MACHEN <amount> <Resource> (create resource).
 *
 * The command creates new resources from inventory and adds them to the executing unit's inventory.
 *
 * - MACHEN <resource>
 * - MACHEN <amount> <resource>
 */
final class Product extends UnitCommand implements Activity
{
	private ?int $demand = null;

	private int $capability = 0;

	/**
	 * The command implementation.
	 */
	protected function run(): void {
		$resource  = $this->phrase->getParameter(0);
		if (count($this->phrase) === 2) {
			$this->demand = (int)$this->phrase->getParameter(1);
		}

		$commodity = $this->context->Factory()->commodity($resource);
		if ($commodity instanceof Material) {
			$this->produceMaterial($commodity);
		} elseif ($commodity instanceof Artifact) {
			$this->produceArtifact($commodity);
		} else {
			throw new LemuriaException('Producing ' . $commodity . ' is not implemented yet.');
		}
	}

	/**
	 * Produce material.
	 *
	 * @param Material $material
	 */
	private function produceMaterial(Material $material): void {
		$talent           = $material->getCraft()->Talent();
		$this->capability = $this->calculateProduction($material->getCraft());
		$resources        = new Resources();
		$reserve          = $this->calculateResources($resources->add(new Quantity($material->getResource(), 1)));
		$production       = min($this->capability, $reserve);
		if ($production > 0) {
			$maxYield = $production * $material->getYield();
			$yield    = $maxYield;
			if ($this->demand !== null) {
				$yield      = min($maxYield, $this->demand);
				$production = (int)ceil($yield / $material->getYield());
			}
			$consumption = new Quantity($material->getResource(), $production);
			$output      = new Quantity($material, $yield);
			$this->unit->Inventory()->remove($consumption);
			$this->unit->Inventory()->add($output);
			if ($this->demand && $this->demand > $maxYield) {
				$this->message(ProductMaterialOnlyMessage::class)->i($output)->s($talent);
			} else {
				$this->message(ProductOutputMessage::class)->i($output)->s($talent);
			}
		} else {
			if ($this->capability > 0) {
				$this->message(ProductOutputMaterialMessage::class)->s($material);
			} else {
				$this->message(ProductOutputExperienceMessage::class)->s($talent, ProductOutputExperienceMessage::TALENT)->s($material, ProductOutputExperienceMessage::MATERIAL);
			}
		}
	}

	/**
	 * Produce artifact.
	 *
	 * @param Artifact $artifact
	 */
	private function produceArtifact(Artifact $artifact): void {
		$talent           = $artifact->getCraft()->Talent();
		$this->capability = $this->calculateProduction($artifact->getCraft());
		$reserve          = $this->calculateResources($artifact->getMaterial());
		$production       = min($this->capability, $reserve);
		if ($production > 0) {
			$yield = $production;
			if ($this->demand !== null) {
				$yield = min($production, $this->demand);
			}
			foreach ($artifact->getMaterial() as $quantity /* @var Quantity $quantity */) {
				$consumption = new Quantity($quantity->Commodity(), $yield * $quantity->Count());
				$this->unit->Inventory()->remove($consumption);
			}
			/* @var Commodity $commodity */
			$commodity = $artifact;
			if (!$commodity instanceof Commodity) {
				throw new LemuriaException('Artifact ' . $artifact . ' is not a Commodity.');
			}
			$output = new Quantity($commodity, $yield);
			$this->unit->Inventory()->add($output);
			if ($this->demand && $this->demand > $production) {
				$this->message(ProductCreateOnlyMessage::class)->i($output)->s($talent);
			} else {
				$this->message(ProductCreateMessage::class)->i($output)->s($talent);
			}
		} else {
			if ($this->capability > 0) {
				$this->message(ProductCreateMaterialMessage::class)->s($artifact);
			} else {
				$this->message(ProductCreateExperienceMessage::class)->s($talent, ProductCreateExperienceMessage::TALENT)->s($artifact, ProductCreateExperienceMessage::ARTIFACT);
			}
		}
	}

	/**
	 * Get maximum amount that can be produced by knowledge.
	 *
	 * @param Requirement $craft
	 * @return int
	 */
	private function calculateProduction(Requirement $craft): int {
		$production = 0;
		$talent     = $craft->Talent();
		$cost       = $craft->Level();
		$level      = $this->calculus()->knowledge(get_class($talent))->Level();
		if ($level >= $cost) {
			$production = (int)floor($this->unit->Size() * $level / $cost);
		}
		return $production;
	}

	/**
	 * Get maximum amount that can be produced by resources.
	 *
	 * @param Resources $resources
	 * @return int
	 */
	private function calculateResources(Resources $resources): int {
		$reserves   = $this->unit->Inventory();
		$production = PHP_INT_MAX;
		foreach ($resources as $quantity /* @var Quantity $quantity */) {
			$commodity = $quantity->Commodity();
			$needed    = $this->capability * $quantity->Count();
			$reserve   = $reserves->offsetGet($commodity)->Count();
			if ($reserve < $needed) {
				$resourcePool = $this->context->getResourcePool($this->unit);
				$resourcePool->take($this->unit, new Quantity($commodity, $needed - $reserve));
			}
			$reserve    = $reserves->offsetGet($commodity);
			$amount     = (int)floor($reserve->Count() / $quantity->Count());
			$production = min($production, $amount);
			if ($production <= 0) {
				break;
			}
		}
		return $production;
	}
}
