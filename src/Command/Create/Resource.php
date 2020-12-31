<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Create;

use Lemuria\Engine\Lemuria\Command;
use Lemuria\Engine\Lemuria\Command\DelegatedCommand;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Lemuria\Artifact as ArtifactInterface;
use Lemuria\Model\Lemuria\Material as MaterialInterface;
use Lemuria\Model\Lemuria\RawMaterial as RawMaterialInterface;

/**
 * Implementation of command MACHEN <amount> <Resource> (create resource).
 *
 * The command creates new resources and adds them to the executing unit's inventory.
 *
 * - MACHEN <resource>
 * - MACHEN <amount> <resource>
 */
final class Resource extends DelegatedCommand
{
	protected function createDelegate(): Command {
		$resource  = $this->phrase->getParameter(0);
		$commodity = $this->context->Factory()->commodity($resource);
		if ($commodity instanceof ArtifactInterface) {
			return new Artifact($this->phrase, $this->context);
		}
		if ($commodity instanceof MaterialInterface) {
			return new Material($this->phrase, $this->context);
		}
		if ($commodity instanceof RawMaterialInterface) {
			return new RawMaterial($this->phrase, $this->context);
		}
		throw new LemuriaException('Producing ' . $commodity . ' is not implemented yet.');
	}
}
