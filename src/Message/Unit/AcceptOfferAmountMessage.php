<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class AcceptOfferAmountMessage extends AcceptOfferRemovedMessage
{
	public final const UNIT = 'unit';

	protected Result $result = Result::FAILURE;

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' does not offer the requested amount in the trade with ID ' . $this->trade . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get(self::UNIT);
	}
}
