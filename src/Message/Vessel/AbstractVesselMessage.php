<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Vessel;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Message\AbstractMessage;
use Lemuria\Engine\Report;

abstract class AbstractVesselMessage extends AbstractMessage
{
	#[Pure] public function Report(): int {
		return Report::VESSEL;
	}
}
