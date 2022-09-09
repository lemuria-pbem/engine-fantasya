<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RegulateNotOwnerMessage extends RegulateNotInsideMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' is not the owner of the market and cannot regulate tradeables.';
	}
}
