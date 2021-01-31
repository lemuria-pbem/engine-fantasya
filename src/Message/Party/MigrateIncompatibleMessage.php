<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Party;

class MigrateIncompatibleMessage extends MigrateFailedMessage
{
	protected function create(): string {
		return 'Unit ' . $this->migrant . ' cannot migrate to party ' . $this->party. '. The races are not compatible.';
	}
}
