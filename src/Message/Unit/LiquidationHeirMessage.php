<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class LiquidationHeirMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has inherited the property of liquidated unit ' . $this->unit . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
