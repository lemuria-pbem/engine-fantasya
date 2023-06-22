<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RealmGuardedMessage extends RealmAlreadyAddedMessage
{
	protected function create(): string {
		return 'This region is guarded by other parties and cannot be incorporated in our realm.';
	}
}
