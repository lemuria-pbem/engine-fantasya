<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RepairUnmaintainedMessage extends CommodityUnmaintainedMessage
{
	public const string BUILDING = parent::BUILDING;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot repair ' . $this->artifact . ' in an unmaintained ' . $this->building . '.';
	}
}
