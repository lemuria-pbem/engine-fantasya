<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Destroy;

use Lemuria\Engine\Lemuria\Activity;
use Lemuria\Engine\Lemuria\Command\UnitCommand;
use Lemuria\Exception\LemuriaException;

/**
 * Implementation of command ZERSTÖREN for constructions and vessels.
 *
 * This command is an ongoing activity that destroys parts of a building/ship until it is completely wiped out. The
 * destroying unit gets back some of the resources that were used when building the building/ship.
 *
 * - ZERSTÖREN Burg|Gebäude|Gebaeude
 * - ZERSTÖREN Schiff
 */
final class Smash extends UnitCommand implements Activity
{
	protected function run(): void {
		//TODO
		throw new LemuriaException('Not implemented yet.');
	}
}
