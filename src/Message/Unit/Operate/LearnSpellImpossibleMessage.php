<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

class LearnSpellImpossibleMessage extends LearnSpellAlreadyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has not enough knowledge in Magic to learn the spell ' . $this->spell . '.';
	}
}
