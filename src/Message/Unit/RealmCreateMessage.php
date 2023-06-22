<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RealmCreateMessage extends RealmAddMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' founds the new realm ' . $this->realm . '.';
	}
}
