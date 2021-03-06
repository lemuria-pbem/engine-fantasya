<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class VesselResourcesMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected Id $vessel;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has no material to build on vessel ' . $this->vessel . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->vessel = $message->get();
	}
}
