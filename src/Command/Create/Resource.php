<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Create;

use Lemuria\Engine\Lemuria\Command;
use Lemuria\Engine\Lemuria\Command\DelegatedCommand;
use Lemuria\Engine\Lemuria\Context;
use Lemuria\Engine\Lemuria\Exception\InvalidCommandException;
use Lemuria\Engine\Lemuria\Factory\Model\Job;
use Lemuria\Engine\Lemuria\Phrase;
use Lemuria\Model\Lemuria\Artifact as ArtifactInterface;
use Lemuria\Model\Lemuria\Material as MaterialInterface;
use Lemuria\Model\Lemuria\RawMaterial as RawMaterialInterface;

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
 * - MACHEN <Material>
 * - MACHEN <amount> <Material>
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
		if ($resource instanceof MaterialInterface) {
			return new Material($this->phrase, $this->context, $this->job);
		}
		if ($resource instanceof RawMaterialInterface) {
			return new RawMaterial($this->phrase, $this->context, $this->job);
		}
		throw new InvalidCommandException($this, 'unknown resource');
	}
}
