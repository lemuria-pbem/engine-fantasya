<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

class RealmRemovedMessage extends RealmAddedMessage
{
	protected function create(): string {
		return 'Region ' . $this->id . ' is being released from realm ' . $this->realm . ' of party ' . $this->party . '.';
	}
}
