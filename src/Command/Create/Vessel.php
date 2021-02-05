<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Create;

use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Lemuria\Ship;

/**
 * Implementation of command MACHEN <Ship> (create ship).
 *
 * The command creates new commodities from inventory and adds them to the executing unit's inventory.
 *
 * - MACHEN <Ship>
 */
final class Vessel extends AbstractProduct
{
	protected function run(): void {
		//TODO
	}

	private function getBuilding(): Ship {
		if ($this->resource instanceof Ship) {
			return $this->resource;
		}
		throw new LemuriaException('Expected a building resource.');
	}
}
