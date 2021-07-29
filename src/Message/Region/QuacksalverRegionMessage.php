<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Item;

class QuacksalverRegionMessage extends AbstractRegionMessage
{
	protected Item $silver;

	protected function create(): string {
		return 'The peasants in region ' . $this->id . ' spend ' . $this->silver . ' for the quacksalver.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->silver = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'silver') ?? parent::getTranslation($name);
	}
}
