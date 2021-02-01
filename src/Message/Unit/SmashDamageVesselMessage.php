<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;

class SmashDamageVesselMessage extends SmashLeaveVesselMessage
{
	protected int $damage;

	protected function create(): string {
		return 'Unit ' . $this->id . ' does ' . $this->damage . ' to the vessel ' . $this->vessel . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->damage = $message->getParameter();
	}
}
