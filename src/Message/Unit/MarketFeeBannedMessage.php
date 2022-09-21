<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class MarketFeeBannedMessage extends MarketFeeNotPaidMessage
{
	protected string $level = Message::EVENT;

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has not paid the market fee, it will not trade this week.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
