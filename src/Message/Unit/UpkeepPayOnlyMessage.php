<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;

class UpkeepPayOnlyMessage extends UpkeepPayMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' can only pay ' . $this->upkeep . ' upkeep for construction ' . $this->construction . '.';
	}
}
