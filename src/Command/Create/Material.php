<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use Lemuria\Engine\Fantasya\Message\Unit\MaterialExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\MaterialResourcesMessage;
use Lemuria\Engine\Fantasya\Message\Unit\MaterialOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\MaterialOutputMessage;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Material as MaterialInterface;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Resources;

/**
 * Implementation of command MACHEN <amount> <Material> (create material).
 *
 * The command creates new materials from inventory or resource pool and adds them to the executing unit's inventory.
 *
 * - MACHEN <Material>
 * - MACHEN <amount> <Material>
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
			$demand   = $this->job->Count();
			$maxYield = $production * $material->getYield();
			$yield    = $maxYield;
			if ($this->job->hasCount()) {
				$yield      = min($maxYield, $demand);
				$production = (int)ceil($yield / $material->getYield());
			}
			$consumption = new Quantity($material->getResource(), $production);
			$output      = new Quantity($material, $yield);
			$this->unit->Inventory()->remove($consumption);
			$this->unit->Inventory()->add($output);
			if ($this->job->hasCount() && $demand > $maxYield) {
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
		$resource = $this->job->getObject();
		if ($resource instanceof MaterialInterface) {
			return $resource;
		}
		throw new LemuriaException('Expected a material resource.');
	}
}
