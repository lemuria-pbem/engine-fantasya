<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Message\Result;

class LearnSpellMessage extends LearnSpellAlreadyMessage
{
	protected Result $result = Result::EVENT;

	protected function create(): string {
		return 'Unit ' . $this->id . ' learns the spell ' . $this->spell . '.';
	}
}
