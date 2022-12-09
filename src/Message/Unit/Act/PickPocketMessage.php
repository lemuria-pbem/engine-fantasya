<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Item;

class PickPocketMessage extends PickPocketRevealedMessage
{
	protected Result $result = Result::Success;

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
