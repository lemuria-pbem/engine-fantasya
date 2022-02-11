<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

class SpellbookNoSpellMessage extends SpellbookWriteAlreadyMessage
{
	protected function create(): string {
		return 'There is no spell named ' . $this->spell . ' in the ' . $this->composition . ' ' . $this->unicum . '.';
	}
}
