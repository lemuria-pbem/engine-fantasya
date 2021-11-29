<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use Lemuria\Engine\Fantasya\Message\Unit\RepairExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RepairResourcesMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RepairCreateMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RepairOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RepairUnmaintainedMessage;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Repairable;

/**
 * Implementation of command MACHEN <amount> <Repairable> (repair commodity).
 *
 * The command transforms repairable items into commodities.
 *
 * - MACHEN <Repairable>
 * - MACHEN <amount> <Repairable>
 */
final class Repair extends AbstractProduct
{
	/** @noinspection DuplicatedCode */
	protected function run(): void {
		$repairable       = $this->getRepairable();
		$talent           = $repairable->getCraft()->Talent();
		$this->capability = $this->calculateProduction($repairable->getCraft());
		$reserve          = $this->calculateResources($repairable->getMaterial());
		$production       = min($this->capability, $reserve);
		if ($production > 0) {
			$count = $this->job->Count();
			$yield = min($production, $count);
			foreach ($repairable->getMaterial() as $quantity /* @var Quantity $quantity */) {
				$count       = (int)ceil($this->consumption * $yield * $quantity->Count());
				$consumption = new Quantity($quantity->Commodity(), $count);
				$this->unit->Inventory()->remove($consumption);
			}
			$commodity = $repairable->Commodity();
			$this->addToWorkload($yield);
			$output = new Quantity($commodity, $yield);
			$this->unit->Inventory()->add($output);
			if ($this->job->hasCount() && $count > $production) {
				$this->message(RepairOnlyMessage::class)->i($output)->s($talent);
			} else {
				$this->message(RepairCreateMessage::class)->i($output)->s($talent);
			}
		} else {
			if ($this->consumption <= 0.0) {
				$building = $this->unit->Construction()->Building();
				$this->message(RepairUnmaintainedMessage::class)->s($repairable)->s($building, RepairUnmaintainedMessage::BUILDING);
			} elseif ($this->capability > 0) {
				$this->message(RepairResourcesMessage::class)->s($repairable);
			} else {
				$this->message(RepairExperienceMessage::class)->s($talent, RepairExperienceMessage::TALENT)->s($repairable, RepairExperienceMessage::ARTIFACT);
			}
		}
	}

	private function getRepairable(): Repairable {
		$resource = $this->job->getObject();
		if ($resource instanceof Repairable) {
			return $resource;
		}
		throw new LemuriaException('Expected an repairable resource.');
	}
}
