<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Item;

class LoseMessage extends LoseEverythingMessage
{
	protected Item $quantity;

	protected function create(): string {
		return 'Unit ' . $this->id . ' throws ' . $this->quantity . ' away.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->quantity = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'quantity') ?? parent::getTranslation($name);
	}
}
