<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class EntertainNoDemandMessage extends AbstractNoDemandMessage
{
	protected function createActivity(): string {
		return 'entertain';
	}
}
