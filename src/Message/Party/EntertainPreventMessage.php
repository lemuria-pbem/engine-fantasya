<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Party;

class EntertainPreventMessage extends AbstractPreventMessage
{
	protected function createActivity(): string {
		return 'entertaining';
	}
}
