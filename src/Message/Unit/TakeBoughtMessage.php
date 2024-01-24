<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Model\Fantasya\Quantity;

class TakeBoughtMessage extends TakeOfferPaymentMessage
{
	protected Result $result = Result::Success;

	protected Quantity $payment;

	protected function create(): string {
		return 'Unit ' . $this->id . ' buys unicum ' . $this->unicum . ' from merchant ' . $this->unit . ' for ' . $this->payment . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->payment = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'payment') ?? parent::getTranslation($name);
	}
}
