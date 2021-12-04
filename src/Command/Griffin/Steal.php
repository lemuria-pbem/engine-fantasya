<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Griffin;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Command\AllocationCommand;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\DefaultActivityTrait;
use Lemuria\Engine\Fantasya\Factory\Model\Job;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Commodity\Griffinegg;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\RawMaterial as RawMaterialInterface;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Requirement;

/**
 * Unit steals griffin eggs.
 */
final class Steal extends AllocationCommand implements Activity
{
	use DefaultActivityTrait;

	protected Ability $knowledge;

	protected ?int $demand = null;

	protected int $production = 0;

	public function __construct(Phrase $phrase, Context $context, protected Job $job) {
		parent::__construct($phrase, $context);
	}

	protected function run(): void {
		parent::run();
	}

	/**
	 * Check region guards before allocation.
	 *
	 * If region is guarded by other parties and there are no RESOURCES relations, this unit may only produce if it is
	 * not in a building and has better camouflage than all the blocking guards' perception.
	 *
	 * @return Party[]
	 */
	protected function getCheckBeforeAllocation(): array {
		return $this->getCheckByAgreement(Relation::RESOURCES);
	}

	/**
	 * Determine the demand.
	 *
	 * @noinspection DuplicatedCode
	 */
	protected function createDemand(): void {
		$requirement      = $this->getRequiredTalent();
		$this->knowledge  = $this->calculus()->knowledge($requirement->Talent());
		$size             = $this->unit->Size();
		$production       = (int)floor($this->potionBoost($size) * $size * $this->knowledge->Level() / $requirement->Level());
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
			$this->message(RawMaterialNoDemandMessage::class)->s($this->getCommodity());
		}
	}

	protected function getCommodity(): Griffinegg {
		$resource = $this->job->getObject();
		if ($resource instanceof Griffinegg) {
			return $resource;
		}
		throw new LemuriaException($resource . ' is not griffin egg.');
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
