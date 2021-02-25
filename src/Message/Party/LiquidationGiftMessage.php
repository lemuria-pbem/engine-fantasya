<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Party;

class LiquidationGiftMessage extends LiquidationMessage
{
	protected function create(): string {
		return 'The inventory of liquidated unit ' . $this->unit . ' has been passed on to other parties in the region.';
	}
}
