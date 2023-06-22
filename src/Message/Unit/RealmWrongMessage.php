<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RealmWrongMessage extends RealmAlreadyAddedMessage
{
	protected function create(): string {
		return 'This region is part of another realm.';
	}
}
