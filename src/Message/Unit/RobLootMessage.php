<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Item;

class RobLootMessage extends RobMessage
{
	protected Item $loot;

	protected function create(): string {
		return 'Unit ' . $this->id . ' robs ' . $this->loot . ' from unit ' . $this->unit . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->loot = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'loot', casus: Casus::Adjective) ?? parent::getTranslation($name);
	}
}
