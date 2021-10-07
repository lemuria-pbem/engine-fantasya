<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class MineUnusableMessage extends MineUnmaintainedMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot produce iron in the mine, not enough space in the pit.';
	}
}
