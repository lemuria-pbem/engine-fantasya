<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Griffin;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Model\Fantasya\Unit;

/**
 * Griffins attack egg-stealing units.
 */
final class Attack extends UnitCommand
{
	/**
	 * @var Unit[]
	 */
	private array $units = [];

	protected function initialize(): void {
		parent::initialize();
	}

	protected function run(): void {
	}
}
