<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class MigrateNotFoundMessage extends GiveFailedMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot migrate, target unit ' . $this->recipient . ' is not here.';
	}
}
