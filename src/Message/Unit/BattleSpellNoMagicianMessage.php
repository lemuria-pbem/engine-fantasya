<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

class BattleSpellNoMagicianMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' is not a magician and cannot cast any combat spells.';
	}
}
