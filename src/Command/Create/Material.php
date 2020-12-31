<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Create;

use Lemuria\Engine\Lemuria\Message\Unit\MaterialExperienceMessage;
use Lemuria\Engine\Lemuria\Message\Unit\MaterialResourcesMessage;
use Lemuria\Engine\Lemuria\Message\Unit\MaterialOnlyMessage;
use Lemuria\Engine\Lemuria\Message\Unit\MaterialOutputMessage;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Lemuria\Material as MaterialInterface;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Resources;

/**
 * Implementation of command MACHEN <amount> <Material> (create material).
 *
 * The command creates new materials from inventory and adds them to the executing unit's inventory.
 *
 * - MACHEN <material>
 * - MACHEN <amount> <material>
 */
final class Material extends AbstractProduct
{
	protected function run(): void {
		$material         = $this->getMaterial();
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
				$this->message(MaterialOnlyMessage::class)->i($output)->s($talent);
			} else {
				$this->message(MaterialOutputMessage::class)->i($output)->s($talent);
			}
		} else {
			if ($this->capability > 0) {
				$this->message(MaterialResourcesMessage::class)->s($material);
			} else {
				$this->message(MaterialExperienceMessage::class)->s($talent, MaterialExperienceMessage::TALENT)->s($material, MaterialExperienceMessage::MATERIAL);
			}
		}
	}

	private function getMaterial(): MaterialInterface {
		$material = $this->context->Factory()->commodity($this->resource);
		if ($material instanceof MaterialInterface) {
			return $material;
		}
		throw new LemuriaException('Expected a material resource.');
	}
}
