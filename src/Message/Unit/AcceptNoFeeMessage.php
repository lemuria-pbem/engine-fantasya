<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class AcceptNoFeeMessage extends AcceptNoMarketMessage
{
	protected string $level = Message::EVENT;

	protected Id $trade;

	protected function create(): string {
		return 'Unit ' . $this->id . ' did not need to pay a market fee for the small trade ' . $this->trade . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->trade = $message->get();
	}
}
