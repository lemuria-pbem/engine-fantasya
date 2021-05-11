<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialCanMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialCannotMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialGuardedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialNoDemandMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialOutputMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialResourcesMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialWantsMessage;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\RawMaterial as RawMaterialInterface;
use Lemuria\Model\Fantasya\Requirement;
use Lemuria\Model\Fantasya\Resources;

/**
 * Implementation of command MACHEN <amount> <RawMaterial> (create raw material).
 *
 * The command creates new resources from region reserve and adds them to the executing unit's inventory.
 *
 * - MACHEN <RawMaterial>
 * - MACHEN <amount> <RawMaterial>
 */
final class Herb extends RawMaterial
{
	public function allocate(Resources $resources): void {
		parent::allocate($resources);
return;
		$resource   = $this->job->getObject();
		$talent     = $this->knowledge->Talent();
		$production = $this->getResource(getClass($resource))->Count();
		if ($production <= 0) {
			if ($this->knowledge->Level() <= 0) {
				$this->message(RawMaterialExperienceMessage::class)->s($talent, RawMaterialExperienceMessage::TALENT)->s($resource, RawMaterialExperienceMessage::MATERIAL);
			} else {
				$guardParties = $this->checkBeforeAllocation();
				if (!empty($guardParties)) {
					$this->message(RawMaterialGuardedMessage::class)->s($resource);
				} else {
					$this->message(RawMaterialResourcesMessage::class)->s($resource);
				}
			}
		} else {
			$this->resources->rewind();
			/* @var Quantity $quantity */
			$quantity = $this->resources->current();
			$this->unit->Inventory()->add($quantity);
			if ($quantity->Count() < $this->production || $this->production < $this->demand) {
				$this->message(RawMaterialOnlyMessage::class)->i($quantity)->s($talent);
			} else {
				$this->message(RawMaterialOutputMessage::class)->i($quantity)->s($talent);
			}
		}
	}

	/**
	 * Determine the demand.
	 */
	protected function createDemand(): void {
		$resource         = $this->getCommodity();
		$requirement      = $this->getRequiredTalent();
		$this->knowledge  = $this->calculus()->knowledge($requirement->Talent());
		$production       = (int)floor($this->unit->Size() * $this->knowledge->Level() / $requirement->Level());
		$this->production = $this->reduceByWorkload($production);
		if ($this->production > 0) {
			if (count($this->phrase) === 2) {
				$this->demand = (int)$this->phrase->getParameter();
				if ($this->demand <= $this->production) {
					$this->production = (int)$this->demand;
					$quantity = new Quantity($this->getCommodity(), $this->production);
					$this->message(RawMaterialWantsMessage::class)->i($quantity);
				} else {
					$quantity = new Quantity($this->getCommodity(), $this->production);
					$this->message(RawMaterialCannotMessage::class)->i($quantity);
				}
			} else {
				$quantity = new Quantity($this->getCommodity(), $this->production);
				$this->message(RawMaterialCanMessage::class)->i($quantity);
			}
			$this->addToWorkload($this->production);
			$this->resources->add($quantity);
		} else {
			$this->message(RawMaterialNoDemandMessage::class)->s($resource);
		}
	}

	protected function getCommodity(): Commodity {
		$resource = $this->job->getObject();
		if ($resource instanceof Commodity) {
			return $resource;
		}
		throw new LemuriaException($resource . ' is not a commodity.');
	}
	/**
	 * Determine the required talent.
	 */
	protected function getRequiredTalent(): Requirement {
		$resource = $this->job->getObject();
		if ($resource instanceof RawMaterialInterface) {
			return $resource->getCraft();
		}
		throw new LemuriaException($resource . ' is not a raw material.');
	}
}
