<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Item;

class AcceptOfferUnableMessage extends AcceptOfferAmountMessage
{
	protected Item $demand;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' wanted to buy ' . $this->demand . ' in trade ' . $this->trade . ', but we had not enough reserve left.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->demand = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'demand') ?? parent::getTranslation($name);
	}
}
