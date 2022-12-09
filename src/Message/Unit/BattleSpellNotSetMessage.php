<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class BattleSpellNotSetMessage extends BattleSpellRemoveMessage
{
	protected Result $result = Result::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot remove ' . $this->spell . ' from battle spells.';
	}
}
