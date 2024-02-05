<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RobNotFightingMessage extends RobSelfMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' must be ready to fight for a robbery attempt.';
	}
}
