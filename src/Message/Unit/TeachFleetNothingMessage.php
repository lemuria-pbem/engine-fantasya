<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TeachFleetNothingMessage extends TeachFleetMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' is fully occupied with realm transport and cannot teach.';
	}
}
