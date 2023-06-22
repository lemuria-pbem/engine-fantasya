<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RealmAnotherMessage extends RealmAlreadyAddedMessage
{
	protected function create(): string {
		return 'This region is already part of another realm.';
	}
}
