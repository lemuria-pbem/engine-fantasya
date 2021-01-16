<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class LeaveVesselMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected Id $vessel;

	protected function create(): string {
		return 'Unit ' . $this->id . ' leaves the vessel ' . $this->vessel . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->vessel = $message->get();
	}
}
