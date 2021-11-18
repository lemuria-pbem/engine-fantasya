<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class LootRemoveGroupMessage extends LootNothingMessage
{
	protected int $group;

	protected function create(): string {
		return 'We will not pick any ' . $this->group . ' for loot.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->group = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'group') {
			return $this->translateKey('loot.group_' . $this->group);
		}
		return parent::getTranslation($name);
	}
}
