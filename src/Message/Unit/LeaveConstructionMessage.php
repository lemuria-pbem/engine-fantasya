<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class LeaveConstructionMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected Id $construction;

	protected function create(): string {
		return 'Unit ' . $this->id . ' leaves the construction ' . $this->construction . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->construction = $message->get();
	}
}
