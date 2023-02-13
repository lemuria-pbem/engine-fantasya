<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class EnterPortDutyPaidMessage extends EnterPortDutyMessage
{
	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' paid ' . $this->duty . ' for duty to the harbour master.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
