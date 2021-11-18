<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

class LootAddGroupMessage extends LootRemoveGroupMessage
{
	protected function create(): string {
		return 'We will pick all ' . $this->group . ' for loot.';
	}
}
