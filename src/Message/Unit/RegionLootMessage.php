<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RegionLootMessage extends ConstructionLootMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' inherits ' . $this->loot . ' from the battle loot in region ' . $this->environment . '.';
	}
}
