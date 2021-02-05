<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class CommodityOnlyMessage extends MaterialOnlyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' can only create ' . $this->output . ' with ' . $this->talent . '.';
	}
}
