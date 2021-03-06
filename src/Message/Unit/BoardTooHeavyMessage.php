<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class BoardTooHeavyMessage extends BoardAlreadyMessage
{
	protected function create(): string {
		return 'Unit '. $this->id . ' is too heavy for boarding the vessel ' . $this->vessel . '.';
	}
}
