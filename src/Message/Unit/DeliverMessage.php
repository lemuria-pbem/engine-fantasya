<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class DeliverMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Success;

	protected Id $unit;

	protected function create(): string {
		return 'Unit '. $this->id . ' sets unit ' . $this->unit . ' free from control.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
