<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Model\Fantasya\Quantity;

class MarketFeePaidMessage extends MarketFeeBannedMessage
{
	protected Result $result = Result::Success;

	protected Quantity $fee;

	protected function create(): string {
		return 'Unit ' . $this->id . ' pays ' . $this->fee . ' for market fee to unit ' . $this->unit . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->fee = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'fee') ?? parent::getTranslation($name);
	}
}
