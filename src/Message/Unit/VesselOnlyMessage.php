<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;

class VesselOnlyMessage extends VesselResourcesMessage
{
	protected int $size;

	protected function create(): string {
		return 'Unit ' . $this->id . ' can only build ' . $this->size . ' points in size on vessel ' . $this->vessel . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->size = $message->getParameter();
	}
}
