<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class LearnFleetNothingMessage extends LearnNotMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' is fully occupied with realm transport and cannot learn ' . $this->talent . '.';
	}
}
