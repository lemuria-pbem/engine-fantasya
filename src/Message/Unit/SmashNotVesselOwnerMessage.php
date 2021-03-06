<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class SmashNotVesselOwnerMessage extends SmashNotInVesselMessage
{
	protected Id $vessel;

	protected function create(): string {
		return 'Unit ' . $this->id . ' must be owner of the vessel ' . $this->vessel . ' to destroy it.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->vessel = $message->get();
	}
}
