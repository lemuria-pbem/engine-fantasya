<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class AcceptTradeUnableMessage extends AcceptForbiddenTradeMessage
{
	public final const CUSTOMER = 'customer';

	protected Id $customer;

	protected function create(): string {
		return 'Unit ' . $this->customer . ' wanted to accept the trade with ID ' . $this->trade . ', but we are unable to satisfy it.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->customer = $message->get(self::CUSTOMER);
	}
}
