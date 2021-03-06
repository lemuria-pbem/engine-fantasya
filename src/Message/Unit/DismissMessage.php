<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Item;

class DismissMessage extends DismissEverythingMessage
{
	protected Item $gift;

	protected function create(): string {
		return 'Unit ' . $this->id . ' donates ' . $this->gift . ' to the peasants.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->gift = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'gift') ?? parent::getTranslation($name);
	}
}
