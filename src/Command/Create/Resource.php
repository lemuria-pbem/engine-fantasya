<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Create;

use Lemuria\Engine\Lemuria\Command;
use Lemuria\Engine\Lemuria\Command\DelegatedCommand;
use Lemuria\Model\Lemuria\Artifact;
use Lemuria\Model\Lemuria\Material;

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
	/**
	 * Create the delegate.
	 *
	 * @return Command
	 */
	protected function createDelegate(): Command {
		$resource  = $this->phrase->getParameter(0);
		$commodity = $this->context->Factory()->commodity($resource);
		if ($commodity instanceof Artifact || $commodity instanceof Material) {
			return new Product($this->phrase, $this->context);
		}
		return new RawMaterial($this->phrase, $this->context);
	}
}
