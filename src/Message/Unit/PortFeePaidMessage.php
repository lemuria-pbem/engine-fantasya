<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class PortFeePaidMessage extends PortFeeMessage
{
	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' pays the port fee ' . $this->fee . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
