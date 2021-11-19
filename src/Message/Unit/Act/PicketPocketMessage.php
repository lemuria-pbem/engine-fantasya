<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Item;

class PicketPocketMessage extends PicketPocketRevealedMessage
{
	protected string $level = Message::SUCCESS;

	protected Item $silver;

	protected function create(): string {
		return 'Unit ' . $this->id . ' picked ' . $this->silver . ' from the pocket of unit ' . $this->enemy . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->silver = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'silver') ?? parent::getTranslation($name);
	}
}
