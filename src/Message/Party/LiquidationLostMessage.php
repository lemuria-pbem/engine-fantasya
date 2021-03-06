<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

class LiquidationLostMessage extends LiquidationMessage
{
    protected function create(): string {
	    return 'The inventory of liquidated unit ' . $this->unit . ' has been lost as there were no heirs in the region.';
    }
}
