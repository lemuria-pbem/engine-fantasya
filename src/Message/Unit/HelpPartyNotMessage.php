<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class HelpPartyNotMessage extends HelpPartyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has withdrawn relation ' . $this->agreement . ' from party ' . $this->party . '.';
	}
}
