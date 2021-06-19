<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class BrainpowerMessage extends AbstractUnitApplyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' suddenly feels clear and concentrated.';
	}
}
