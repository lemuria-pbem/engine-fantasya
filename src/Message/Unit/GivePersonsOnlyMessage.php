<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

class GivePersonsOnlyMessage extends GivePersonsMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' can only transfer ' . $this->persons . ' persons to unit ' . $this->recipient . '.';
	}
}
