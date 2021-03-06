<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message;

class MigrateFailedMessage extends MigrateFromMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->migrant . ' cannot migrate to party ' . $this->party. '. The recipient refused to accept the unit.';
	}
}
