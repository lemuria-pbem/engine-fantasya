<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Message\Result;

class RealmDisconnectedMessage extends RealmAddedMessage
{
	protected Result $result = Result::Failure;

	protected function create(): string {
		return 'Region ' . $this->id . ' is disconnected from realm ' . $this->realm . ' of party ' . $this->party . '.';
	}
}
