<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

class LootCommodityNotMessage extends LootCommodityMessage
{
	protected function create(): string {
		return 'We will not pick any ' . $this->commodity . ' for loot.';
	}
}
