<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use function Lemuria\getClass;
use function Lemuria\randArray;
use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Command\AllocationCommand;
use Lemuria\Engine\Fantasya\Command\Explore;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\DefaultActivityTrait;
use Lemuria\Engine\Fantasya\Factory\Model\Herb as HerbModel;
use Lemuria\Engine\Fantasya\Factory\Model\Job;
use Lemuria\Engine\Fantasya\Factory\Model\RealmQuota;
use Lemuria\Engine\Fantasya\Message\Party\HerbPreventMessage;
use Lemuria\Engine\Fantasya\Message\Unit\HerbExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\HerbGuardedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\HerbNoDemandMessage;
use Lemuria\Engine\Fantasya\Message\Unit\HerbNoneMessage;
use Lemuria\Engine\Fantasya\Message\Unit\HerbUnknownMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialOutputMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RawMaterialWantsMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Herbage;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Herballore;

/**
 * Implementation of command MACHEN Kräuter (create herb).
 *
 * - MACHEN Kraut|Kraeuter|Kräuter
 * - MACHEN <amount> Kraut|Kraeuter|Kräuter
 */
final class Herb extends AllocationCommand implements Activity
{
	use DefaultActivityTrait;

	public final const int LEVEL = 3;

	private int $production = 0;

	private int $demand = 0;

	private int $level = 0;

	private ?float $threshold = null;

	private Talent $herballore;

	public function __construct(Phrase $phrase, Context $context, private Job $job) {
		parent::__construct($phrase, $context);
		$this->herballore = self::createTalent(Herballore::class);
		if ($job->hasCount()) {
			$this->demand = $job->Count();
		}
		$threshold = $job->Threshold();
		if (is_float($threshold)) {
			$this->threshold = $threshold;
		}
	}

	public function canBeCentralized(): bool {
		return true;
	}

	public function allows(Activity $activity): bool {
		if ($activity instanceof Explore) {
			return true;
		}
		return getClass($activity) === getClass($this);
	}

	protected function run(): void {
		parent::run();
		if ($this->resources->Count() <= 0) {
			if ($this->level < self::LEVEL) {
				$this->message(HerbExperienceMessage::class)->s($this->herballore, RawMaterialExperienceMessage::TALENT);
			} else {
				$guardParties = $this->checkBeforeAllocation();
				if (!empty($guardParties)) {
					$this->message(HerbGuardedMessage::class);
					foreach ($guardParties as $party) {
						$this->message(HerbPreventMessage::class, $party)->e($this->unit);
					}
				} elseif (!$this->isAlternative()) {
					$this->message(HerbNoneMessage::class)->e($this->unit->Region());
				}
			}
		} else {
			foreach ($this->resources as $quantity) {
				$this->unit->Inventory()->add($quantity);
				if ($quantity->Count() < $this->production || $this->demand > $this->production) {
					$this->message(RawMaterialOnlyMessage::class)->i($quantity)->s($this->herballore);
				} else {
					$this->message(RawMaterialOutputMessage::class)->i($quantity)->s($this->herballore);
				}
			}
		}
	}

	/**
	 * @return array<Party>
	 */
	protected function getCheckBeforeAllocation(): array {
		return $this->getCheckByAgreement(Relation::RESOURCES);
	}

	protected function createDemand(): void {
		$this->level = $this->getProductivity(Herballore::class)->Level();
		if ($this->level >= self::LEVEL) {
			$this->production = (int)floor($this->unit->Size() * $this->level / self::LEVEL);
			$this->production = $this->reduceByWorkload($this->production);
			if ($this->demand > 0 && $this->demand < $this->production) {
				$this->production = $this->demand;
			}
			/** @var Commodity $job */
			$job = $this->job->getObject();
			if ($this->isRunCentrally) {
				if ($job instanceof HerbModel) {
					$this->createCentralDemand();
				} else {
					$this->reduceDemandByAvailability($job);
					$this->createSimpleDemand($job);
				}
			} else {
				$region = $this->unit->Region();
				$herb   = $this->determineHerbage($region)?->Herb();
				if ($herb) {
					if ($this->threshold === null) {
						$this->reduceDemandByQuota($region, $herb);
					}
					if ($job instanceof HerbModel) {
						$job = $herb;
					}
					$this->createSimpleDemand($job);
				} else {
					$this->message(HerbUnknownMessage::class)->e($region);
				}
			}
		} else {
			$this->message(HerbNoDemandMessage::class);
		}
	}

	protected function undoProduction(): void {
		parent::undoProduction();
		$this->undoWorkload($this->production);
	}

	protected function getImplicitThreshold(): int|float|null {
		return $this->threshold;
	}

	private function createCentralDemand(): void {
		$herbs = [];
		foreach ($this->allotment->Realm()->Territory() as $region) {
			$herbage = $this->determineHerbage($region);
			if ($herbage) {
				$herb       = $herbage->Herb();
				$maximum    = $this->context->getAvailability($region)->MaxHerbs();
				$occurrence = (int)round($herbage->Occurrence() * $maximum);
				$threshold  = (int)round($this->determineThreshold($herb, $region) * $maximum);
				if ($occurrence > $threshold) {
					$herbs[$herb::class] = true;
				}
			}
		}
		$herbs = array_keys($herbs);
		$n     = count($herbs);
		if ($n > 0) {
			$this->addToWorkload($this->production);
			if ($this->production < $n) {
				$herbs = randArray($herbs, $this->production);
				$n     = $this->production;
			}
			$rate = (int)floor($this->production / $n);
			$rest = $this->production % $n;
			foreach ($herbs as $class) {
				$quantity = new Quantity(self::createCommodity($class), $rate + ($rest-- > 0 ? 1 : 0));
				$this->resources->add($quantity);
				$this->message(RawMaterialWantsMessage::class)->i($quantity);
			}
		} else {
			$this->message(HerbUnknownMessage::class)->e($this->unit->Region());
		}
	}

	private function reduceDemandByQuota(Region $region, Commodity $herb): void {
		$quota = $this->unit->Party()->Regulation()->getQuotas($region)?->getQuota($herb)?->Threshold();
		if (is_float($quota) && $quota > 0) {
			$available = $this->context->getAvailability($region)->getQuotaResource($herb, $quota)->Count();
			if ($available < $this->production) {
				Lemuria::Log()->debug('Availability of ' . $herb . ' reduced due to quota.');
				$this->production = $available;
				if ($this->demand > $available) {
					$this->demand = $available;
				}
			}
		}
	}

	private function reduceDemandByAvailability(Commodity $herb): void {
		$available = $this->allotment->getAvailability($this, $herb);
		if ($available < $this->production) {
			Lemuria::Log()->debug('Availability of ' . $herb . ' reduced due to quota.');
			$this->production = $available;
			if ($this->demand > $available) {
				$this->demand = $available;
			}
		}
	}

	private function createSimpleDemand(Commodity $herb): void {
		$this->addToWorkload($this->production);
		$quantity = new Quantity($herb, $this->production);
		$this->resources->add($quantity);
		$this->message(RawMaterialWantsMessage::class)->i($quantity);
	}

	private function determineHerbage(Region $region): ?Herbage {
		$party      = $this->unit->Party();
		$herbalBook = $party->HerbalBook();
		if ($herbalBook->contains($region)) {
			return $herbalBook->getHerbage($region);
		}
		return null;
	}

	private function determineThreshold(Commodity $herb, Region $region): float {
		if (is_float($this->threshold)) {
			return $this->threshold;
		}
		if ($this->allotment) {
			$quotas = new RealmQuota($this->allotment->Realm());
			$quota  = $quotas->getQuota($region, $herb)->Threshold();
			return is_float($quota) ? $quota : 0.0;
		}
		$quota = $this->unit->Party()->Regulation()->getQuotas($region)?->getQuota($herb)?->Threshold();
		if (is_float($quota)) {
			return $quota;
		}
		return 0.0;
	}
}
