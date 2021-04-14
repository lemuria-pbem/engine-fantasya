<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Item;

class RawMaterialWantsMessage extends AbstractUnitMessage
{
	protected Item $quantity;

	protected function create(): string {
		return 'Unit ' . $this->id . ' wants to produce ' . $this->quantity . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->quantity = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'quantity') ?? parent::getTranslation($name);
	}
}
