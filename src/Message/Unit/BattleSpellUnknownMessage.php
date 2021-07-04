<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class BattleSpellUnknownMessage extends BattleSpellNoMagicianMessage
{
	protected string $spell;

	protected function create(): string {
		return 'Unit ' . $this->id . ' will not cast ' . $this->spell . ' in combat anymore.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->spell = $message->getParameter();
	}
}
