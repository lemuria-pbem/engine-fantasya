<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Merchant;
use Lemuria\Model\Fantasya\Region;

/**
 * This exception is thrown by the Commerce class.
 */
class CommerceException extends \InvalidArgumentException
{
	/**
	 * Create an exception for a Merchant that was not considered in a commerce.
	 */
	#[Pure] public function __construct(Merchant $merchant, Region $region) {
		$message = 'The merchant ' . $merchant->getId() . ' is not part of commerce in region ' . $region->Id() . '.';
		parent::__construct($message);
	}
}
