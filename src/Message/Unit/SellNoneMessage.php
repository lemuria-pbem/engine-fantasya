<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SellNoneMessage extends BuyNoneMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot sell any ' . $this->goods . ' to the peasants.';
	}
}
