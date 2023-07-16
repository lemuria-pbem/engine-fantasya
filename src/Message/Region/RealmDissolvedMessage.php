<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

class RealmDissolvedMessage extends RealmAddedMessage
{
	protected function create(): string {
		return 'The realm ' . $this->realm . ' has been dissolved by party ' . $this->party . '.';
	}
}
