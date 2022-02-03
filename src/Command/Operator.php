<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Model\Fantasya\Unicum;
use Lemuria\Model\Fantasya\Unit;

interface Operator
{
	public function Unit(): Unit;

	public function Unicum(): Unicum;
}
