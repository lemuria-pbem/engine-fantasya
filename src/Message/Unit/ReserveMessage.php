<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Item;

class ReserveMessage extends ReserveEverythingMessage
{
	protected Item $reserve;

	protected function create(): string {
		return 'Unit ' . $this->id . ' reserves ' . $this->reserve . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->reserve = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'reserve') ?? parent::getTranslation($name);
	}
}
