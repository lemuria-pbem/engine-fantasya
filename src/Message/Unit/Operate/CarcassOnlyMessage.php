<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Reliability;
use Lemuria\Item;

class CarcassOnlyMessage extends CarcassNothingMessage
{
	protected Reliability $reliability = Reliability::Determined;

	protected Item $item;

	protected function create(): string {
		return 'Unit ' . $this->id . ' can only find ' . $this->item . ' in ' . $this->composition . ' ' . $this->unicum . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->item = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'item') ?? parent::getTranslation($name);
	}
}
