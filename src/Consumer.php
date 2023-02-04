<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Resources;

/**
 * Consumers are commands that apply for resource allocation.
 */
interface Consumer extends Command
{
	/**
	 * Get the requested resources.
	 */
	public function getDemand(): Resources;

	/**
	 * Get the requested resource quota that is available for allocation.
	 */
	public function getQuota(): float;

	/**
	 * Check diplomacy between the unit and region owner and guards.
	 *
	 * This method should return the foreign parties that prevent executing the
	 * command.
	 *
	 * @return array<Party>
	 */
	public function checkBeforeAllocation(): array;

	/**
	 * Allocate resources.
	 */
	public function allocate(Resources $resources): void;
}
