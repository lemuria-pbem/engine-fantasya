<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

class PresettingNoLootMessage extends PresettingLootMessage
{
	protected function create(): string {
		return 'New recruits will not gather loot by default.';
	}
}
