<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Create;

use Lemuria\Engine\Lemuria\Command;
use Lemuria\Engine\Lemuria\Command\DelegatedCommand;
use Lemuria\Engine\Lemuria\Context;
use Lemuria\Engine\Lemuria\Exception\UnknownCommandException;
use Lemuria\Engine\Lemuria\Factory\Model\Job;
use Lemuria\Engine\Lemuria\Phrase;
use Lemuria\Model\Lemuria\Commodity as CommodityInterface;
use Lemuria\Model\Lemuria\Building;
use Lemuria\Model\Lemuria\Ship;

/**
 * Implementation of command MACHEN <amount> <artifact> (create artifact).
 *
 * The command creates new artifacts from inventory and adds them to the executing unit's inventory.
 *
 * - MACHEN Burg|Gebäude|Gebaeude|Schiff
 * - MACHEN Burg|Gebäude|Gebaeude|Schiff <size>
 * - MACHEN <Building>
 * - MACHEN <Building> <size>
 * - MACHEN <Ship>
 * - MACHEN <Ship> <size>
 * - MACHEN <Commodity>
 * - MACHEN <amount> <Commodity>
 */
final class Artifact extends DelegatedCommand
{
	public function __construct(Phrase $phrase, Context $context, private Job $job) {
		parent::__construct($phrase, $context);
	}

	protected function createDelegate(): Command {
		$resource = $this->job->getObject();
		if ($resource instanceof Building) {
			return new Construction($this->phrase, $this->context, $this->job);
		}
		if ($resource instanceof Ship) {
			return new Vessel($this->phrase, $this->context, $this->job);
		}
		if ($resource instanceof CommodityInterface) {
			return new Commodity($this->phrase, $this->context, $this->job);
		}
		throw new UnknownCommandException($this);
	}
}
