<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

class SpyMessage extends SpyOwnUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has successfully spied on unit ' . $this->unit . '.';
	}
}
