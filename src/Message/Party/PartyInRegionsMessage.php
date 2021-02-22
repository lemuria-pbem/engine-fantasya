<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Party;

class PartyInRegionsMessage extends PartyVisitMessage
{
	protected function create(): string {
		return 'Party ' . $this->id . ' has units in ' . $this->regions . ' regions.';
	}
}
