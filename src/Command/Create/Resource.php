<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Command\DelegatedCommand;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\Model\Job;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Fantasya\Artifact as ArtifactInterface;
use Lemuria\Model\Fantasya\Building\Sawmill;
use Lemuria\Model\Fantasya\Commodity\Wood;
use Lemuria\Model\Fantasya\RawMaterial as RawMaterialInterface;

/**
 * Implementation of command MACHEN <amount> <Resource> (create resource).
 *
 * The command creates new resources and adds them to the executing unit's inventory.
 *
 * - MACHEN Burg|Gebäude|Gebaeude|Schiff
 * - MACHEN Burg|Gebäude|Gebaeude|Schiff <size>
 * - MACHEN <Artifact>
 * - MACHEN <Artifact> <size>
 * - MACHEN <amount> <Artifact>
 * - MACHEN <RawMaterial>
 * - MACHEN <amount> <RawMaterial>
 */
final class Resource extends DelegatedCommand
{
	public function __construct(Phrase $phrase, Context $context, private Job $job) {
		parent::__construct($phrase, $context);
	}

	protected function createDelegate(): Command {
		$resource = $this->job->getObject();
		if ($resource instanceof ArtifactInterface) {
			return new Artifact($this->phrase, $this->context, $this->job);
		}
		if ($resource instanceof Wood && $this->unit->Construction()?->Building() instanceof Sawmill) {
			return new SawmillWood($this->phrase, $this->context, $this->job);
		}
		if ($resource instanceof RawMaterialInterface) {
			return new RawMaterial($this->phrase, $this->context, $this->job);
		}
		throw new InvalidCommandException($this, 'unknown resource');
	}
}
