<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

class LearnSpellUnableMessage extends LearnSpellAlreadyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot learn the spell $spell now.';
	}
}
