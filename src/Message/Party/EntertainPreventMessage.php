<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

class EntertainPreventMessage extends AbstractPreventMessage
{
	protected function createActivity(): string {
		return 'entertaining';
	}
}
