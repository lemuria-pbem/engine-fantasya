<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class AcceptNoFeeReceivedMessage extends AcceptNoFeeMessage
{
	public final const UNIT = 'unit';

	protected string $level = Message::EVENT;

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' did not pay a market fee for the small trade ' . $this->trade . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get(self::UNIT);
	}
}
