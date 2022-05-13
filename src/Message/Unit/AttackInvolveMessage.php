<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AttackInvolveMessage extends AttackMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' attacks unit ' . $this->unit . '.';
	}
}
