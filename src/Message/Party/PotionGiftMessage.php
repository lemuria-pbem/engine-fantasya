<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;
use Lemuria\Item;

class PotionGiftMessage extends AbstractPartyMessage
{
	protected Result $result = Result::Event;

	protected Id $unit;

	protected Item $gift;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' has met an old travelling sorcerer and alchemist. He had a gift for us: ' . $this->gift . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
		$this->gift = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'gift') ?? parent::getTranslation($name);
	}
}
