<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\Model\Job;
use Lemuria\Engine\Fantasya\Message\Unit\CommodityExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CommodityResourcesMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CommodityCreateMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CommodityOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CommodityUnmaintainedMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Artifact as ArtifactInterface;
use Lemuria\Model\Fantasya\Commodity as CommodityModel;
use Lemuria\Model\Fantasya\Commodity\Protection\LeatherArmor;
use Lemuria\Model\Fantasya\Quantity;

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
	private const EFFICIENCY = [
		LeatherArmor::class => 5.0
	];

	public function __construct(Phrase $phrase, Context $context, protected Job $job) {
		parent::__construct($phrase, $context, $this->job);
		$this->setEfficiency();
	}

	protected function run(): void {
		$artifact         = $this->getArtifact();
		$talent           = $artifact->getCraft()->Talent();
		$this->capability = $this->calculateProduction($artifact->getCraft());
		$reserve          = $this->calculateResources($artifact->getMaterial());
		$production       = min($this->capability, $reserve);
		if ($production > 0) {
			$jobCount = $this->job->Count();
			$yield    = min($production, $jobCount);
			foreach ($artifact->getMaterial() as $quantity /* @var Quantity $quantity */) {
				$count       = (int)ceil($this->consumption * $yield * $quantity->Count() / $this->efficiency);
				$consumption = new Quantity($quantity->Commodity(), $count);
				$this->unit->Inventory()->remove($consumption);
			}
			/* @var CommodityModel $commodity */
			$commodity = $artifact;
			if (!$commodity instanceof CommodityModel) {
				throw new LemuriaException('Artifact ' . $artifact . ' is not a Commodity.');
			}
			$this->addToWorkload($yield);
			$output = new Quantity($commodity, $yield);
			$this->unit->Inventory()->add($output);
			if ($this->job->hasCount() && $jobCount > $production) {
				$this->message(CommodityOnlyMessage::class)->i($output)->s($talent);
			} else {
				$this->message(CommodityCreateMessage::class)->i($output)->s($talent);
			}
		} else {
			if ($this->consumption <= 0.0) {
				$building = $this->unit->Construction()->Building();
				$this->message(CommodityUnmaintainedMessage::class)->s($artifact)->s($building, CommodityUnmaintainedMessage::BUILDING);
			} elseif ($this->capability > 0) {
				$this->message(CommodityResourcesMessage::class)->s($artifact);
			} else {
				$this->message(CommodityExperienceMessage::class)->s($talent, CommodityExperienceMessage::TALENT)->s($artifact, CommodityExperienceMessage::ARTIFACT);
			}
		}
	}

	private function setEfficiency(): void {
		$artifact = $this->job->getObject();
		$class    = $artifact::class;
		if (isset(self::EFFICIENCY[$class])) {
			$this->efficiency = self::EFFICIENCY[$class];
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
