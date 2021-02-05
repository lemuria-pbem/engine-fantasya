<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Create;

use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Lemuria\Building;

/**
 * Implementation of command MACHEN <amount> <Commodity> (create artifact).
 *
 * The command creates new commodities from inventory and adds them to the executing unit's inventory.
 *
 * - MACHEN <Commodity>
 * - MACHEN <amount> <Commodity>
 */
final class Construction extends AbstractProduct
{
	protected function run(): void {
		//TODO
	}

	private function getBuilding(): Building {
		if ($this->resource instanceof Building) {
			return $this->resource;
		}
		throw new LemuriaException('Expected a building resource.');
	}
}
