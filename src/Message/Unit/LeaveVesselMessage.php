<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class LeaveVesselMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Success;

	protected Id $vessel;

	protected function create(): string {
		return 'Unit ' . $this->id . ' leaves the vessel ' . $this->vessel . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->vessel = $message->get();
	}
}
