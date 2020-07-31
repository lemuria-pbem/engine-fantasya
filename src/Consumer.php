<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use Lemuria\Model\Lemuria\Party;
use Lemuria\Model\Lemuria\Resources;

/**
 * Consumers are commands that apply for resource allocation.
 */
interface Consumer extends Command
{
	/**
	 * Get the requested resources.
	 *
	 * @return Resources
	 */
	public function getDemand(): Resources;

	/**
	 * Get the requested resource quota that is available for allocation.
	 *
	 * @return float
	 */
	public function getQuota(): float;

	/**
	 * Check diplomacy between the unit and region owner and guards.
	 *
	 * This method should return the foreign parties that prevent executing the
	 * command.
	 *
	 * @return Party[]
	 */
	public function checkBeforeAllocation(): array;

	/**
	 * Allocate resources.
	 *
	 * @param Resources $resources
	 * @return bool
	 */
	public function allocate(Resources $resources): bool;
}
