<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class GuardBattleRowMessage extends GuardAlreadyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot guard as it is not fighting.';
	}
}
