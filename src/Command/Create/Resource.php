<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Create;

use Lemuria\Engine\Lemuria\Command;
use Lemuria\Engine\Lemuria\Command\DelegatedCommand;
use Lemuria\Engine\Lemuria\Exception\InvalidCommandException;
use Lemuria\Model\Lemuria\Artifact as ArtifactInterface;
use Lemuria\Model\Lemuria\Material as MaterialInterface;
use Lemuria\Model\Lemuria\RawMaterial as RawMaterialInterface;

/**
 * Implementation of command MACHEN <amount> <Resource> (create resource).
 *
 * The command creates new resources and adds them to the executing unit's inventory.
 *
 * - MACHEN <Artifact>
 * - MACHEN <amount> <Artifact>
 * - MACHEN <Material>
 * - MACHEN <amount> <Material>
 * - MACHEN <RawMaterial>
 * - MACHEN <amount> <RawMaterial>
 */
final class Resource extends DelegatedCommand
{
	protected function createDelegate(): Command {
		$what     = $this->phrase->getParameter(0);
		$resource = $this->context->Factory()->resource($what);
		if ($resource instanceof ArtifactInterface) {
			return new Artifact($this->phrase, $this->context, $resource);
		}
		if ($resource instanceof MaterialInterface) {
			return new Material($this->phrase, $this->context, $resource);
		}
		if ($resource instanceof RawMaterialInterface) {
			return new RawMaterial($this->phrase, $this->context, $resource);
		}
		throw new InvalidCommandException($this, 'unknown resource');
	}
}
