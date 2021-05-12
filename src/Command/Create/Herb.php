<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\Model\Job;
use Lemuria\Engine\Fantasya\Message\Unit\HerbUnknownMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Fantasya\Herbage;
use Lemuria\Engine\Fantasya\Factory\Model\Herb as HerbModel;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Herb as HerbInterface;

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
		$this->herbage = $this->unit->Party()->HerbalBook()->getHerbage($this->unit->Region());
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

	protected function getCommodity(): Commodity {
		$resource = $this->job->getObject();
		if ($resource instanceof HerbModel) {
			return $this->herbage->Herb();
		}
		if ($resource instanceof HerbInterface) {
			return $resource;
		}
		throw new LemuriaException($resource . ' is not a herb.');
	}
}
