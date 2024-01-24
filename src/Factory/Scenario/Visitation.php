<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Scenario;

use Lemuria\Model\Fantasya\Unit;
use Lemuria\StringList;

interface Visitation
{
	public function from(Unit $unit): StringList;
}
