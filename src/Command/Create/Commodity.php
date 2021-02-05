<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Create;

use Lemuria\Engine\Lemuria\Message\Unit\CommodityExperienceMessage;
use Lemuria\Engine\Lemuria\Message\Unit\CommodityResourcesMessage;
use Lemuria\Engine\Lemuria\Message\Unit\CommodityCreateMessage;
use Lemuria\Engine\Lemuria\Message\Unit\CommodityOnlyMessage;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Lemuria\Artifact as ArtifactInterface;
use Lemuria\Model\Lemuria\Commodity as CommodityModel;
use Lemuria\Model\Lemuria\Quantity;

/**
 * Implementation of command MACHEN <amount> <Commodity> (create artifact).
 *
 * The command creates new commodities from inventory and adds them to the executing unit's inventory.
 *
 * - MACHEN <Commodity>
 * - MACHEN <amount> <Commodity>
 */
final class Commodity extends AbstractProduct
{
	protected function run(): void {
		$artifact         = $this->getArtifact();
		$talent           = $artifact->getCraft()->Talent();
		$this->capability = $this->calculateProduction($artifact->getCraft());
		$reserve          = $this->calculateResources($artifact->getMaterial());
		$production       = min($this->capability, $reserve);
		if ($production > 0) {
			$count = $this->job->Count();
			$yield = min($production, $count);
			foreach ($artifact->getMaterial() as $quantity /* @var Quantity $quantity */) {
				$consumption = new Quantity($quantity->Commodity(), $yield * $quantity->Count());
				$this->unit->Inventory()->remove($consumption);
			}
			/* @var CommodityModel $commodity */
			$commodity = $artifact;
			if (!$commodity instanceof CommodityModel) {
				throw new LemuriaException('Artifact ' . $artifact . ' is not a Commodity.');
			}
			$output = new Quantity($commodity, $yield);
			$this->unit->Inventory()->add($output);
			if ($this->job->hasCount() && $count > $production) {
				$this->message(CommodityOnlyMessage::class)->i($output)->s($talent);
			} else {
				$this->message(CommodityCreateMessage::class)->i($output)->s($talent);
			}
		} else {
			if ($this->capability > 0) {
				$this->message(CommodityResourcesMessage::class)->s($artifact);
			} else {
				$this->message(CommodityExperienceMessage::class)->s($talent, CommodityExperienceMessage::TALENT)->s($artifact, CommodityExperienceMessage::ARTIFACT);
			}
		}
	}

	private function getArtifact(): ArtifactInterface {
		$resource = $this->job->getObject();
		if ($resource instanceof ArtifactInterface) {
			return $resource;
		}
		throw new LemuriaException('Expected an artifact resource.');
	}
}
