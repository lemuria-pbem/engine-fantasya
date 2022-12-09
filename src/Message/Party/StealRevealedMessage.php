<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class StealRevealedMessage extends AbstractPartyMessage
{
	public final const UNIT = 'unit';

	protected Result $result = Result::Event;

	protected Id $region;

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' has tried to steal from us in region ' . $this->region . ' but failed.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get();
		$this->unit   = $message->get(self::UNIT);
	}
}
