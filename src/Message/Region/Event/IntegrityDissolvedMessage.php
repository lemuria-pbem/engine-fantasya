<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region\Event;

use Lemuria\Engine\Fantasya\Message\Region\RealmFoundedMessage;

class IntegrityDissolvedMessage extends RealmFoundedMessage
{
	protected function create(): string {
		return 'The party ' . $this->party . ' has destroyed the realm ' . $this->realm . '.';
	}
}
