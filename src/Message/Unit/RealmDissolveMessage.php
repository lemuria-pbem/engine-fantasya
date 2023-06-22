<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RealmDissolveMessage extends RealmAddMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' dissolves the realm ' . $this->realm . '.';
	}
}
