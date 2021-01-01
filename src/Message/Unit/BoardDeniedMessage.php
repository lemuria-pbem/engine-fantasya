<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class BoardDeniedMessage extends BoardAlreadyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot board the vessel ' . $this->vessel . ' without permission from captain.';
	}
}
