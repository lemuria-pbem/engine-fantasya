<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Region;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Message\AbstractMessage;
use Lemuria\Engine\Report;

abstract class AbstractRegionMessage extends AbstractMessage
{
	#[Pure] public function Report(): int {
		return Report::LOCATION;
	}
}
