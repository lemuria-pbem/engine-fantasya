<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class HelpPartyRegionNotMessage extends HelpPartyRegionMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has withdrawn relation ' . $this->agreement . ' from party ' . $this->party . ' in region ' . $this->region . '.';
	}
}
