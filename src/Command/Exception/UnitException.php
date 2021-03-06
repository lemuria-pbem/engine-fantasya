<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Exception;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Unit;

/**
 * This exception is thrown when executing a Unit command fails.
 */
class UnitException extends CommandException
{
	#[Pure] public function __construct(Unit $unit, Party $party) {
		$message = 'Unit ' . $unit->Id() . ' is not a member of party ' . $party->Id() . '.';
		parent::__construct($message);
	}
}
