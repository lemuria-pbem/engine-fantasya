<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Singleton;

class AcceptNoPaymentMessage extends AcceptOfferRemovedMessage
{
	protected Result $result = Result::FAILURE;

	protected Singleton $payment;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has not enough ' . $this->payment . ' to pay the offer ' . $this->trade . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->payment = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->commodity($name, 'payment', 1)?? parent::getTranslation($name);
	}
}
