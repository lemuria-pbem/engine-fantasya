<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Create;

use Lemuria\Engine\Lemuria\Message\Unit\ArtifactExperienceMessage;
use Lemuria\Engine\Lemuria\Message\Unit\ArtifactResourcesMessage;
use Lemuria\Engine\Lemuria\Message\Unit\ArtifactCreateMessage;
use Lemuria\Engine\Lemuria\Message\Unit\ArtifactOnlyMessage;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Lemuria\Artifact as ArtifactInterface;
use Lemuria\Model\Lemuria\Commodity;
use Lemuria\Model\Lemuria\Quantity;

/**
 * Implementation of command MACHEN <amount> <artifact> (create artifact).
 *
 * The command creates new artifacts from inventory and adds them to the executing unit's inventory.
 *
 * - MACHEN <artifact>
 * - MACHEN <amount> <artifact>
 */
final class Artifact extends AbstractProduct
{
	/**
	 * The command implementation.
	 */
	protected function run(): void {
		parent::run();
		$artifact         = $this->getArtifact();
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
				$this->message(ArtifactOnlyMessage::class)->i($output)->s($talent);
			} else {
				$this->message(ArtifactCreateMessage::class)->i($output)->s($talent);
			}
		} else {
			if ($this->capability > 0) {
				$this->message(ArtifactResourcesMessage::class)->s($artifact);
			} else {
				$this->message(ArtifactExperienceMessage::class)->s($talent, ArtifactExperienceMessage::TALENT)->s($artifact, ArtifactExperienceMessage::ARTIFACT);
			}
		}
	}

	/**
	 * @return ArtifactInterface
	 */
	private function getArtifact(): ArtifactInterface {
		$artifact = $this->context->Factory()->commodity($this->resource);
		if ($artifact instanceof ArtifactInterface) {
			return $artifact;
		}
		throw new LemuriaException('Expected an artifact resource.');
	}
}
