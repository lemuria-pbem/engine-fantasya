<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class ForbidAllMessage extends AllowAllMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' forbids to trade all goods.';
	}
}
