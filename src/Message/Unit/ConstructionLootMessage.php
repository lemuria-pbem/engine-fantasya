<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class ConstructionLootMessage extends AbstractLootMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' inherits ' . $this->loot . ' from the battle loot in construction ' . $this->environment . '.';
	}
}
