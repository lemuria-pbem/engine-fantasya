<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Factory\Model\Everything;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Item;

class GiveRejectedMessage extends GiveFailedMessage
{
	protected Item $gift;

	protected function create(): string {
		$gift = $this->gift->getObject() instanceof Everything ? 'all its property' : $this->gift;
		return 'Unit ' . $this->recipient . ' wanted to give ' . $this->id . ' ' . $gift . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->gift = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'gift') ?? parent::getTranslation($name);
	}
}
