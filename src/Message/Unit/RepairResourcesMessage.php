<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RepairResourcesMessage extends CommodityResourcesMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has no material to repair ' . $this->artifact . '.';
	}
}
