<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class HelpRegionNotMessage extends HelpRegionMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has withdrawn relation ' . $this->agreement . ' from all parties in region ' . $this->region . '.';
	}
}
