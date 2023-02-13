<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Item;

class TravelCanalPaidMessage extends TravelTooHeavyMessage
{
	protected Result $result = Result::Event;

	protected Item $fee;

	protected function create(): string {
		return 'Unit ' . $this->id . ' pays ' . $this->fee . ' as canal fee.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->fee = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'fee') ?? parent::getTranslation($name);
	}
}
