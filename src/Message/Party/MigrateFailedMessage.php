<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message\Result;

class MigrateFailedMessage extends MigrateFromMessage
{
	protected Result $result = Result::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->migrant . ' cannot migrate to party ' . $this->party. '. The recipient refused to accept the unit.';
	}
}
