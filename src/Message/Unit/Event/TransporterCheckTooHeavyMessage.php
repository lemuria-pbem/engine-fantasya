<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Event;

class TransporterCheckTooHeavyMessage extends TransporterCheckNoRidingMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' is too heavy for transporting.';
	}
}
