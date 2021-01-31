<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Party;

use Lemuria\Engine\Message;

class MigrateRejectedMessage extends MigrateToMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->migrant . ' wanted to migrate to party ' . $this->id . '.';
	}
}
