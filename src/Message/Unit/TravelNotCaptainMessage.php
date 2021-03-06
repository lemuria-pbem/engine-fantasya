<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class TravelNotCaptainMessage extends TravelTooHeayMessage
{
	protected Id $vessel;

	protected function create(): string {
		return 'Unit ' . $this->id . ' must be the captain to steer the vessel ' . $this->vessel . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->vessel = $message->get();
	}
}
