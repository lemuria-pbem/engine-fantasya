<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception;

use Lemuria\Engine\Fantasya\Consumer;
use Lemuria\Model\Fantasya\Region;

/**
 * This exception is thrown by the Allocation class.
 */
class AllocationException extends \InvalidArgumentException
{
	/**
	 * Create an exception for a Consumer that was not considered in an allocation.
	 */
	public function __construct(Consumer $consumer, Region $region) {
		$message = 'The consumer ' . $consumer->getId() . ' is not part of allocation in region ' . $region->Id() . '.';
		parent::__construct($message);
	}
}
