<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Command\Explore;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\Model\Herb as HerbModel;
use Lemuria\Engine\Fantasya\Factory\Model\Job;
use Lemuria\Engine\Fantasya\Message\Unit\HerbUnknownMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Herb\Elvendear;
use Lemuria\Model\Fantasya\Herb as HerbInterface;
use Lemuria\Model\Fantasya\Herbage;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Herballore;

/**
 * Implementation of command MACHEN Kräuter (create herb).
 *
 * - MACHEN Kraut|Kraeuter|Kräuter
 * - MACHEN <amount> Kraut|Kraeuter|Kräuter
 */
final class Herb extends RawMaterial
{
	private ?Herbage $herbage;

	public function __construct(Phrase $phrase, Context $context, Job $job) {
		parent::__construct($phrase, $context, $job);
		$this->knowledge = new Ability(self::createTalent(Herballore::class), Ability::getExperience(3));
		$this->determineHerbage();
	}

	public function allows(Activity $activity): bool {
		if ($activity instanceof Explore) {
			return true;
		}
		return parent::allows($activity);
	}

	public function getCommodity(): Commodity {
		$resource = $this->job->getObject();
		if ($resource instanceof HerbModel) {
			return $this->herbage?->Herb() ?? self::createCommodity(Elvendear::class); // TODO: Improve for Allotment.
		}
		if ($resource instanceof HerbInterface) {
			return $resource;
		}
		throw new LemuriaException($resource . ' is not a herb.');
	}

	/**
	 * Determine the demand.
	 */
	protected function createDemand(): void {
		if (!$this->herbage) {
			$this->message(HerbUnknownMessage::class)->e($this->unit->Region());
			return;
		}
		parent::createDemand();
	}

	protected function runForEmptyDemand(Talent $talent, Commodity $resource): void {
		if ($this->herbage) {
			parent::runForEmptyDemand($talent, $resource);
		}
	}

	protected function productionDone(): void {
	}

	protected function determineHerbage(): void {
		$herbalBook = $this->unit->Party()->HerbalBook();
		$region     = $this->unit->Region();
		$realm      = $region->Realm();
		if ($realm) {
			foreach ($realm->Territory() as $region) {
				$this->herbage = $herbalBook->getHerbage($region);
				if ($this->herbage) {
					break;
				}
			}
		} else {
			$this->herbage = $herbalBook->getHerbage($region);
		}
	}
}
