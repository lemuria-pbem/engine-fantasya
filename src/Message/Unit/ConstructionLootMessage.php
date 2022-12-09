<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;
use Lemuria\Item;

class ConstructionLootMessage extends AbstractUnitMessage
{
	protected Result $result = Result::EVENT;

	protected Id $environment;

	protected Item $loot;

	protected function create(): string {
		return 'Unit ' . $this->id . ' inherits ' . $this->loot . ' from the battle loot in construction ' . $this->environment . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->environment = $message->get();
		$this->loot        = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'loot') ?? parent::getTranslation($name);
	}
}
