<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class AcceptForbiddenTradeMessage extends AcceptNoMarketMessage
{
	protected Id $trade;

	protected function create(): string {
		return 'The trade with ID ' . $this->trade . ' has been forbidden by the market owner.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->trade = $message->get();
	}
}
