<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Message\AbstractMessage;
use Lemuria\Engine\Report;

abstract class AbstractConstructionMessage extends AbstractMessage
{
	#[ExpectedValues(valuesFromClass: Report::class)]
	#[Pure] public function Report(): int {
		return Report::CONSTRUCTION;
	}
}
