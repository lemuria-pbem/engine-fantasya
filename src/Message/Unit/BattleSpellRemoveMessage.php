<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Singleton;

class BattleSpellRemoveMessage extends BattleSpellRemoveAllMessage
{
	protected Singleton $spell;

	protected function create(): string {
		return 'Unit ' . $this->id . ' will not cast ' . $this->spell . ' in combat anymore.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->spell = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->spell($name, 'spell') ?? parent::getTranslation($name);
	}
}
