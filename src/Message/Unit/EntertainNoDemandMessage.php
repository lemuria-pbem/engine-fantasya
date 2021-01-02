<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;

class EntertainNoDemandMessage extends AbstractNoDemandMessage
{
	protected function createActivity(): string {
		return 'entertain';
	}
}
