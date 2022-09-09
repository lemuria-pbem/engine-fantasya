<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class ForbidCommodityMessage extends AllowCommodityMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' forbids to trade ' . $this->commodity . '.';
	}
}
