<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Message;

class SpellbookWriteAlreadyMessage extends SpellbookWriteMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot write the spell ' . $this->spell . ' in the ' . $this->composition . ' ' . $this->unicum . ' a second time.';
	}
}
