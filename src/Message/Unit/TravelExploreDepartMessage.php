<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TravelExploreDepartMessage extends TravelExploreLandMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has landed on a new shore and departed again.';
	}
}
