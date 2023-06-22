<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\Unit\RealmAddMessage;

class RealmRemovedMessage extends RealmAddMessage
{
	protected function create(): string {
		return 'Region ' . $this->id . ' is being released from realm ' . $this->realm . '.';
	}
}
