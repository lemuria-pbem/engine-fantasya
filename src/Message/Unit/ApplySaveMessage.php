<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class ApplySaveMessage extends ApplyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' saves ' . $this->potion . '.';
	}
}
