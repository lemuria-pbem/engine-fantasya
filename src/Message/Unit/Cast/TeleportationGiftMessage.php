<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

class TeleportationGiftMessage extends TeleportationMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has inherited the property of teleported unit ' . $this->unit . '.';
	}
}
