<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class VesselLootMessage extends ConstructionLootMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' inherits ' . $this->loot . ' from the battle loot on vessel ' . $this->environment . '.';
	}
}
