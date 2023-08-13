<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class NameRealmFirstTimeMessage extends NameRealmMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has entitled the realm "' . $this->name . '".';
	}
}
