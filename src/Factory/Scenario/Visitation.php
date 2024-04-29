<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Scenario;

use Lemuria\Engine\Fantasya\Factory\Model\Buzzes;
use Lemuria\Model\Fantasya\Unit;

interface Visitation
{
	public function from(Unit $unit): Buzzes;
}
