<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class LiquidationHeirMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Success;

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has inherited the property of liquidated unit ' . $this->unit . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
