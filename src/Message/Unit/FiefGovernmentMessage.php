<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class FiefGovernmentMessage extends FiefNoneMessage
{
	protected function create(): string {
		return 'A realm must be handed over by the owning unit in the government castle.';
	}
}
