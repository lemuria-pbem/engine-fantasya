<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\Unit\RealmAddMessage;

class RealmDissolvedMessage extends RealmAddMessage
{
	protected function create(): string {
		return 'The realm ' . $this->realm . ' has been dissolved.';
	}
}
