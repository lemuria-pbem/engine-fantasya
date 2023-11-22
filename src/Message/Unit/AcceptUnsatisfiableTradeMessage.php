<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class AcceptUnsatisfiableTradeMessage extends AcceptForbiddenTradeMessage
{
	public final const string MERCHANT = 'merchant';

	protected Id $merchant;

	protected function create(): string {
		return 'Unit ' . $this->merchant . ' cannot satisfy the trade with ID ' . $this->trade . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->merchant = $message->get(self::MERCHANT);
	}
}
