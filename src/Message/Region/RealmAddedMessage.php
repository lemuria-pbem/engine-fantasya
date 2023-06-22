<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

class RealmAddedMessage extends RealmFoundedMessage
{
	protected function create(): string {
		return 'Region ' . $this->id . ' is now part of realm ' . $this->realm . ' of party ' . $this->party . '.';
	}
}
