<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Message\Result;

class BrokenCarriageMessage extends AbstractRegionMessage
{
	protected Result $result = Result::EVENT;

	protected function create(): string {
		return 'A ragged orc on his wasted carriage is rumbling across the region, when suddenly he looses control ' .
			'over the horses and crashes.';
	}
}
