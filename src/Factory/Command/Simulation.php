<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Factory\DefaultActivityTrait;

final class Simulation extends UnitCommand implements Activity
{
	use DefaultActivityTrait;
}
