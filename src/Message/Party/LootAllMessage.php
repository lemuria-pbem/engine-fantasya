<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

class LootAllMessage extends LootNothingMessage
{
	protected function create(): string {
		return 'We will pick all loot.';
	}
}
