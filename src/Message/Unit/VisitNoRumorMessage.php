<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Reliability;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class VisitNoRumorMessage extends VisitNoMarketMessage
{
	protected Result $result = Result::Success;

	protected Reliability $reliability = Reliability::Unreliable;

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has not heard any rumors from unit ' . $this->unit . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
