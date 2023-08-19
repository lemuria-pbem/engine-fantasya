<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Availability;
use Lemuria\Engine\Fantasya\Command\AllocationCommand;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\DefaultActivityTrait;
use Lemuria\Engine\Fantasya\Factory\LodgingTrait;
use Lemuria\Engine\Fantasya\Factory\Model\Job;
use Lemuria\Engine\Fantasya\Message\Unit\MineUnmaintainedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\MineUnusableMessage;
use Lemuria\Engine\Fantasya\Message\Unit\QuarryUnmaintainedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\QuarryUnusableMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialCanMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialCannotMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialGuardedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialNoDemandMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialOutputMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialResourcesMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialWantsMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SawmillUnmaintainedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SawmillUnusableMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Building\Mine;
use Lemuria\Model\Fantasya\Building\Quarry;
use Lemuria\Model\Fantasya\Building\Sawmill;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Iron;
use Lemuria\Model\Fantasya\Commodity\Stone;
use Lemuria\Model\Fantasya\Commodity\Wood;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\RawMaterial as RawMaterialInterface;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Requirement;
use Lemuria\Model\Fantasya\Talent;

/**
 * Implementation of command MACHEN <amount> <RawMaterial> (create raw material).
 *
 * The command creates new resources from region reserve and adds them to the executing unit's inventory.
 *
 * - MACHEN <RawMaterial>
 * - MACHEN <amount> <RawMaterial>
 */
class RawMaterial extends AllocationCommand implements Activity
{
	use DefaultActivityTrait;
	use LodgingTrait;

	protected Ability $knowledge;

	protected ?int $demand = null;

	protected int $production = 0;

	protected int $available = 0;

	protected bool $isInDoublingFacility = false;

	protected bool $hasLodging = false;

	public function __construct(Phrase $phrase, Context $context, protected Job $job) {
		parent::__construct($phrase, $context);
	}

	public function canBeCentralized(): bool {
		return true;
	}

	protected function initialize(): void {
		$this->checkForDoubleProductionFacility();
		if ($this->isInDoublingFacility) {
			$this->hasLodging = $this->bookLodging();
		}
		parent::initialize();
	}

	protected function run(): void {
		parent::run();
		$resource   = $this->getCommodity();
		$talent     = $this->knowledge->Talent();
		$production = $this->getResource(getClass($resource))->Count();
		if ($production <= 0) {
			$this->runForEmptyDemand($talent, $resource);
		} else {
			$this->resources->rewind();
			$quantity = $this->resources->current();
			$production = $this->production;
			if ($this->hasLodging) {
				$quantity->multiply(2);
				$production *= 2;
			}
			$this->unit->Inventory()->add($quantity);
			if ($quantity->Count() < $production || $production < $this->demand) {
				$this->message(RawMaterialOnlyMessage::class)->i($quantity)->s($talent);
			} else {
				$this->message(RawMaterialOutputMessage::class)->i($quantity)->s($talent);
			}
			$this->productionDone($quantity);
		}
	}

	/**
	 * Check region guards before allocation.
	 *
	 * If region is guarded by other parties and there are no RESOURCES relations, this unit may only produce if it is
	 * not in a building and has better camouflage than all the blocking guards' perception.
	 *
	 * @return array<Party>
	 */
	protected function getCheckBeforeAllocation(): array {
		return $this->getCheckByAgreement(Relation::RESOURCES);
	}

	protected function createDemand(): void {
		if ($this->hasLodging) {
			if ($this->calculus()->isInMaintainedConstruction()) {
				$this->createMultipleDemand(2);
				return;
			}
			$this->addUnmaintainedMessage();
			$this->hasLodging = false;
		} elseif ($this->isInDoublingFacility) {
			$this->addUnusableMessage();
		}
		$this->createMultipleDemand();
	}

	protected function getCommodity(): Commodity {
		$resource = $this->job->getObject();
		if ($resource instanceof Commodity) {
			return $resource;
		}
		throw new LemuriaException($resource . ' is not a commodity.');
	}

	protected function getImplicitThreshold(): int|float|null {
		return $this->job->Threshold();
	}

	protected function getAvailability(): int {
		$commodity = $this->getCommodity();
		if ($this->isRunCentrally) {
			return $this->allotment->getAvailability($this, $commodity);
		}

		$region    = $this->unit->Region();
		$resources = $region->Resources();
		$reserve   = $resources[$commodity]->Count();
		if ($this->job->hasThreshold()) {
			$quota = $this->job->Threshold();
		} else {
			$quota = $this->unit->Party()->Regulation()->getQuotas($region)?->getQuota($commodity)?->Threshold();
		}
		if (is_int($quota) && $quota > 0) {
			Lemuria::Log()->debug('Availability of ' . $commodity . ' reduced due to quota.');
			return max(0, $reserve - $quota);
		}
		if (is_float($quota) && $quota < 1.0) {
			Lemuria::Log()->debug('Availability of ' . $commodity . ' reduced due to quota.');
			$pieces = Availability::HERBS_PER_REGION * $quota;
			return max(0, $reserve - $pieces);
		}
		return $reserve;
	}

	protected function getRequiredTalent(): Requirement {
		$resource = $this->job->getObject();
		if ($resource instanceof RawMaterialInterface) {
			return $resource->getCraft();
		}
		throw new LemuriaException($resource . ' is not a raw material.');
	}

	protected function runForEmptyDemand(Talent $talent, Commodity $resource): void {
		if ($this->knowledge->Level() <= 0) {
			$this->message(RawMaterialExperienceMessage::class)->s($talent, RawMaterialExperienceMessage::TALENT)->s($resource, RawMaterialExperienceMessage::MATERIAL);
		} else {
			$guardParties = $this->checkBeforeAllocation();
			if (!empty($guardParties)) {
				$this->message(RawMaterialGuardedMessage::class)->s($resource);
			} else {
				if ($this->available <= 0 && !$this->isAlternative()) {
					$this->message(RawMaterialResourcesMessage::class)->s($resource);
				}
			}
		}
	}

	protected function productionDone(Quantity $quantity): void {
	}

	private function checkForDoubleProductionFacility(): void {
		$resource = $this->job->getObject();
		$building = $this->unit->Construction()?->Building();
		if ($resource instanceof Wood && $building instanceof Sawmill) {
			$this->isInDoublingFacility = true;
		} elseif ($resource instanceof Stone && $building instanceof Quarry) {
			$this->isInDoublingFacility = true;
		} elseif ($resource instanceof Iron && $building instanceof Mine) {
			$this->isInDoublingFacility = true;
		}
	}

	private function createMultipleDemand(int $factor = 1): void {
		$talent          = $this->getRequiredTalent();
		$this->knowledge = $this->getProductivity($talent);
		if ($this->knowledge->Level() < $talent->Level()) {
			$this->knowledge = new Ability($this->knowledge->Talent(), 0);
		}

		$size             = $this->unit->Size();
		$production       = (int)floor($this->potionBoost($size) * $size * $this->knowledge->Level() / $talent->Level());
		$this->production = $this->reduceByWorkload($production);
		$this->available  = $factor * $this->getAvailability();

		if ($this->production > 0 && $this->available > 0) {
			if ($this->available < $this->production) {
				$this->production = $this->available;
			}
			if ($this->job->hasCount()) {
				$this->demand = max(0, $this->job->Count());
				if ($this->demand <= $this->production) {
					$this->production = (int)ceil($this->demand / $factor);
					$quantity         = new Quantity($this->getCommodity(), $this->production);
					$this->message(RawMaterialWantsMessage::class)->i($quantity);
				} else {
					$quantity = new Quantity($this->getCommodity(), $this->production);
					$this->message(RawMaterialCannotMessage::class)->i($quantity);
				}
				$this->addToWorkload($this->production);
				$this->resources->add($quantity);
			} else {
				$debugProduction  = $this->production;
				$this->production = (int)ceil($this->production / $factor);
				if ($this->production > 0) {
					$quantity = new Quantity($this->getCommodity(), $this->production);
					$this->addToWorkload($this->production);
					$this->resources->add($quantity);
					$quantity = new Quantity($this->getCommodity(), $debugProduction);
					$this->message(RawMaterialCanMessage::class)->i($quantity);
				} else {
					$this->message(RawMaterialNoDemandMessage::class)->s($this->getCommodity());
				}
			}
		} else {
			$this->message(RawMaterialNoDemandMessage::class)->s($this->getCommodity());
		}
	}

	protected function addUnusableMessage(): void {
		$resource = $this->getCommodity();
		switch ($resource::class) {
			case Wood::class :
				$this->message(SawmillUnusableMessage::class);
				break;
			case Stone::class :
				$this->message(QuarryUnusableMessage::class);
				break;
			case Iron::class :
				$this->message(MineUnusableMessage::class);
				break;
			default :
				throw new LemuriaException('Unsupported resource ' . getClass($resource));
		}
	}

	protected function addUnmaintainedMessage(): void {
		$resource = $this->getCommodity();
		switch ($resource::class) {
			case Wood::class :
				$this->message(SawmillUnmaintainedMessage::class);
				break;
			case Stone::class :
				$this->message(QuarryUnmaintainedMessage::class);
				break;
			case Iron::class :
				$this->message(MineUnmaintainedMessage::class);
				break;
			default :
				throw new LemuriaException('Unsupported resource ' . getClass($resource));
		}
	}
}
