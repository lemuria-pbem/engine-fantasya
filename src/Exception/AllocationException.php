<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Exception;

use Lemuria\Engine\Lemuria\Consumer;
use Lemuria\Model\Lemuria\Region;

/**
 * This exception is thrown by the Allocation class.
 */
class AllocationException extends \InvalidArgumentException {

	/**
	 * Create an exception for a Consumer that was not considered in an allocation.
	 *
	 * @param Consumer $consumer
	 * @param Region $region
	 */
	public function __construct(Consumer $consumer, Region $region) {
		$message = 'The consumer ' . $consumer->getId() . ' is not part of allocation in region ' . $region->Id() . '.';
		parent::__construct($message);
	}
}
