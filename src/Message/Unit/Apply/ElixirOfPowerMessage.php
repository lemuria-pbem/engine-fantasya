<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Apply;

class ElixirOfPowerMessage extends AbstractApplyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' feels invincible.';
	}
}
