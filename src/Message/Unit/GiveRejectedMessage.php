<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Item;

class GiveRejectedMessage extends GiveFailedMessage
{
	protected Item $gift;

	protected function create(): string {
		return 'Unit ' . $this->recipient . ' wanted to give ' . $this->id . ' ' . $this->gift . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->gift = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'gift') ?? parent::getTranslation($name);
	}
}
