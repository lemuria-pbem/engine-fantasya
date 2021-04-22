<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class GivePersonsNoSpaceMessage extends GiveNoPersonsMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot give any persons to unit ' . $this->recipient . ' - no more room in the construction.';
	}
}
