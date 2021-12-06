<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

class DaydreamProductivityMessage extends DaydreamConcentrateMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' is daydreaming and distracted from its work.';
	}
}
