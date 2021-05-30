<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;
use Lemuria\Item;

class GiveReceivedMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected Id $from;

	protected Item $gift;

	protected function create(): string {
		return 'Unit ' . $this->id . ' receives ' . $this->gift . ' from unit ' . $this->from . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->from = $message->get();
		$this->gift = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'gift') ?? parent::getTranslation($name);
	}
}
