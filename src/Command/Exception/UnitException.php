<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Exception;

use Lemuria\Engine\Lemuria\Exception\CommandException;
use Lemuria\Model\Lemuria\Party;
use Lemuria\Model\Lemuria\Unit;

/**
 * This exception is thrown when executing a Unit command fails.
 */
class UnitException extends CommandException
{
	/**
	 * Create exception.
	 *
	 * @param Unit $unit
	 * @param Party $party
	 */
	public function __construct(Unit $unit, Party $party) {
		$message = 'Unit ' . $unit->Id() . ' is not a member of party ' . $party->Id() . '.';
		parent::__construct($message);
	}
}
