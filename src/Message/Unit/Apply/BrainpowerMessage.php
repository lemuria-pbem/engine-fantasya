<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Apply;

class BrainpowerMessage extends AbstractApplyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' suddenly feels clear and concentrated.';
	}
}
