<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class LeaveNewCaptainMessage extends AbstractVesselMessage
{
	protected Id $captain;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' has new captain ' . $this->captain . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->captain = $message->get();
	}
}
