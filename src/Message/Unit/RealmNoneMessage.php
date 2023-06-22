<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RealmNoneMessage extends RealmAlreadyAddedMessage
{
	protected function create(): string {
		return 'This region is not part of any realm.';
	}
}
